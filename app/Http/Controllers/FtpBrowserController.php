<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFTPRequest;
use App\Setting;
use App\User;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\AdminSetting;


class FtpBrowserController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('dashboard::office.index', compact('arrTabs', 'active'));
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function ftpManager()
    {
        return view('ftp.manager');
    }


    public function setFTPCredentialsAdmin()
    {
        $admin = User::where('admin', 1)->first();
        $adminSettings = AdminSetting::where('user_id', $admin->id)->first();

        return view('ftp.credentials_admin', compact('adminSettings'));
    }

    public function setFTPCredentials()
    {
        return view('ftp.credentials');
    }

    public function CheckFTPConnectionType()
    {
        if (!isset(Auth::user()->admin_setting->use_admin_ftp_credentials) || Auth::user()->admin_setting->use_admin_ftp_credentials == 'N') {
            if (Auth::user()->setting->ftp_type === 'ftp') {
                config(['filesystems.disks.ftp' => [
                    'driver' => 'ftp',
                    'host' => Auth::user()->setting->ftp_host,
                    'username' => Auth::user()->setting->ftp_user_name,
                    'password' => Auth::user()->setting->ftp_password
                ]]);
            } elseif (Auth::user()->setting->ftp_type === 'sftp') {
                config(['filesystems.disks.sftp' => [
                    'driver' => 'sftp',
                    'host' => Auth::user()->setting->ftp_host,
                    'username' => Auth::user()->setting->ftp_user_name,
                    'password' => Auth::user()->setting->ftp_password,
                    'root' => '/',
                    'timeout' => 10,
                    'port' => 22,
                ]]);
            }
        }

    }

    /**
     * Get content of FTP root folder
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function ftpContent()
    {


        try {
            //-- Choose what type of FTP credentials to use
            $this->CheckFTPConnectionType();

            $arrData = array();

            $arrFolders = Storage::disk(Auth::user()->setting->ftp_type)->directories('/');

            if (!empty($arrFolders)) {
                foreach ($arrFolders as $key => $folder) {
                    $arrData[] = [
                        'id' => "root_" . $key . "_folder",
                        'data' => $folder,
                        'text' => $folder,
                        'icon' => ""
                    ];
                }
            }

            $arrFiles = Storage::disk(Auth::user()->setting->ftp_type)->files('/');
            if (!empty($arrFiles)) {
                foreach ($arrFiles as $key => $file) {
                    $arrData[] = [
                        'id' => "root_" . $key . "_file",
                        'text' => $file,
                        'data' => "/",
                        'icon' => "jstree-file"
                    ];
                }
            }

            if (!empty($arrData)) {
                $arrData = json_encode($arrData, true);
            }

        } catch (\Exception $e) {
            $arrOptions = [
                'message' => trans('messages.ftp_could_not_connect'),
                'type' => 'error',
                'position' => 'bottomLeft'
            ];
            Session::flash('ftp_change', $arrOptions);
            return redirect()->route('office_ftp_connection', ['id' => Auth::id()]);
        }

        return view('ftp.browser', compact('arrData'));
    }

    public function ajaxCreateFolder(Request $request)
    {
        $folderId = $request['folderId'];
        $folderNewName = $request['folderNewName'];
        $folderPath = $request['folderPath'];
        $strError = "";
        $result = "success";
        $root = false;
        $arrData=[];
        $key=0;

        $this->CheckFTPConnectionType();

        $exists = Storage::disk(Auth::user()->setting->ftp_type)->exists($folderPath . "/" . $folderNewName);
        if (!$exists) {
            Storage::disk(Auth::user()->setting->ftp_type)->makeDirectory($folderPath . "/" . $folderNewName);

            $countFolders = "";
            $arrFolders = Storage::disk(Auth::user()->setting->ftp_type)->directories('/' . $folderPath);
            if (!empty($arrFolders)) {
                foreach ($arrFolders as $key => $path) {
                    $countFolders = $key;
                }
            }

            $key = $countFolders + 1;
            if (empty($folderPath)) {
                $folderItemId = "root_" . $key . "_folder";
                $root = true;
            } else {
                $folderItemId = $folderId . "_" . $key . "_folder";
            }
            $arrData[] = [
                'id' => $folderItemId,
                'data' => $folderPath . "/" . $folderNewName,
                'text' => $folderNewName,
                'icon' => ""
            ];
        } else {
            $result = "";
            $strError = trans('messages.folder_with_name_exist', ['name' => $folderNewName]);
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError,
            'arrData' => $arrData,
            'root' => $root,
            'folderId' => $folderId,
            'new_file_id' => $folderId . "_" . $key . "_folder"
        ));

    }

    /**
     * Functionality to download specific file
     *
     * @param $file
     * @return mixed
     */
    public function downloadFile($file)
    {
        $this->CheckFTPConnectionType();
        return Storage::disk(Auth::user()->setting->ftp_type)->download($file);
    }


    /**
     * Functionality to delete specific folder from FTP
     *
     * @param Request $request
     */
    public function ajaxDeleteFolder(Request $request)
    {
        $folderPath = $request['folderPath'];
        $strError = "";
        $result = "success";

        try {
            $this->CheckFTPConnectionType();
            Storage::disk(Auth::user()->setting->ftp_type)->deleteDirectory($folderPath);
        } catch (\Exception $e) {
            $result = "";
            $strError = trans('messages.cant_delete_folder');
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));
    }

    public function ajaxGetFileData(Request $request)
    {
        $fileName = $request['fileName'];
        $filePath = $request['filePath'];
        $strError = "";
        $result = "success";
        $arrData = [];

        $this->CheckFTPConnectionType();
        $size = Storage::disk(Auth::user()->setting->ftp_type)->size('/' . $filePath);
        $last_modified = Storage::disk(Auth::user()->setting->ftp_type)->lastModified('/' . $filePath);
        $last_modified = DateTime::createFromFormat("U", $last_modified);

        $arrFilePath = explode("/", $filePath);
        if (count($arrFilePath) > 1) {
            unset($arrFilePath[count($arrFilePath) - 1]);
            $strFolder = implode("/", $arrFilePath);
        } else {
            $strFolder = $arrFilePath[count($arrFilePath) - 1];
        }
        $arrData['name'] = $fileName;
        $arrData['size'] = $size;
        $arrData['last_modified'] = $last_modified;
        $arrData['folder'] = $strFolder;

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError,
            'arrData' => $arrData,
        ));
    }

    /**
     * Functionality to delete specific file from required folder on FTP server
     * @param Request $request
     */
    public function ajaxDeleteFile(Request $request)
    {
        $filePath = $request['filePath'];
        $strError = "";
        $result = "success";

        try {
            $this->CheckFTPConnectionType();
            Storage::disk(Auth::user()->setting->ftp_type)->delete($filePath);
        } catch (\Exception $e) {
            $result = "";
            $strError = trans('messages.cant_delete_file');
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));
    }

    public function uploadFile(Request $request)
    {
        $strError = "";
        $result = "success";
        $folder_path = $request->folder_path;
        $folder_id = !empty($request->folder_id) ? $request->folder_id : 'root';
        $arrData = [];
        // $arrAllowedExtension = ['png', 'jpg', 'jpeg','txt','pdf','xls','xls'];

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $this->CheckFTPConnectionType();

        // if (in_array($extension, $arrAllowedExtension)) {
        if (!($file->getClientSize() > 2100000)) {
            $name = $file->getClientOriginalName();

            request()->file('file')->storeAs(
                'public/upload/' . Auth::id() . '/files/', $name
            );

            $exists = Storage::disk(Auth::user()->setting->ftp_type)->exists($folder_path . '/' . $name);
            if (!$exists) {
                $file_local = Storage::disk('local')->get('/public/upload/' . Auth::id() . '/files/' . $name);
                Storage::disk(Auth::user()->setting->ftp_type)->put($folder_path . '/' . $name, $file_local);


                $countFiles = "";
                $arrFiles = Storage::disk(Auth::user()->setting->ftp_type)->files('/' . $folder_path);
                if (!empty($arrFiles)) {
                    foreach ($arrFiles as $key => $path) {
                        $countFiles = $key;
                    }
                }
                $key = $countFiles + 1;
                $arrData[] = [
                    'id' => $folder_id . "_" . $key . "_file",
                    'text' => $name,
                    'data' => '/' . $folder_path,
                    'icon' => "jstree-file"
                ];
            } else {
                $result = "";
                $strError = trans('messages.file_with_name_exist', ['name' => $name]);
            }

            unlink(storage_path('/app/public/upload/' . Auth::id() . '/files/' . $name));


        } else {
            $result = trans('messages.max_allowed_size');
        }
//        } else {
//            $result = trans('images::messages.image_format_error',['formats'=>implode(",", $arrAllowedExtension)]);
//        }


        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError,
            'arrData' => $arrData,
            'folderId' => $folder_id
        ));
    }


    /**
     * Generate folder content for specific FTP folder
     *
     * @param Request $request
     */
    public function ajaxGetFolderContent(Request $request)
    {

        $strId = $request['id'];
        $strPath = $request['path'];
        $strError = "";
        $result = "success";
        $arrFolderData = [];
        $intFilesCount = 0;
        $intFoldersCount = 0;

        //-- Initialize array of folder children
        $arrData = array();
        try {

            $this->CheckFTPConnectionType();

            $arrFolders = Storage::disk(Auth::user()->setting->ftp_type)->directories('/' . $strPath);
            if (!empty($arrFolders)) {
                foreach ($arrFolders as $key => $path) {
                    $arrPath = explode("/", $path);
                    $folder = $arrPath[count($arrPath) - 1];
                    $arrData[] = [
                        'id' => $strId . "_" . $key . "_folder",
                        'data' => $path,
                        'text' => $folder,
                        'icon' => ""
                    ];
                    $intFoldersCount++;
                }
            }


            $arrFiles = Storage::disk(Auth::user()->setting->ftp_type)->files('/' . $strPath);
            if (!empty($arrFiles)) {
                foreach ($arrFiles as $key => $path) {
                    $arrPath = explode("/", $path);
                    $file = $arrPath[count($arrPath) - 1];
                    $arrData[] = [
                        'id' => $strId . "_" . $key . "_file",
                        'text' => $file,
                        'data' => '/' . $strPath,
                        'icon' => "jstree-file"
                    ];
                    $intFilesCount++;
                }
            }
        } catch (\Exception $e) {
            $strError = trans('messages.cant_connect_remote_server');
            $result = "";
        }


        $arrFolderData['folder_name'] = $strPath;
        $arrFolderData['files'] = $intFilesCount;
        $arrFolderData['folders'] = $intFoldersCount;

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError,
            'arrData' => $arrData,
            'arrFolderData' => $arrFolderData,
            'test_folders' => $arrFolders,
            'test_path' => '/' . $strPath,
        ));

    }


    /**
     * Store FTP password for admin user (in .env file)
     *
     * @param CreateFTPRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFTPCredentialsAdmin(CreateFTPRequest $request)
    {

        $user = Auth::user();
        $ftp_host = $request->ftp_host;
        $ftp_user_name = $request->ftp_user_name;
        $ftp_password = $request->ftp_password;

        setEnvironmentValue('FTP_HOST', $ftp_host);
        setEnvironmentValue('FTP_USER_NAME', $ftp_user_name);
        setEnvironmentValue('FTP_PASS', $ftp_password);

        Session::flash('ftp_change', trans('messages.ftp_credentials_were_updated'));
        return redirect()->route('office_ftp_connection_admin', ['id' => $user->id]);

    }

    /**
     * Store FTP password for specific user
     *
     * @param CreateFTPRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFTPCredentials(CreateFTPRequest $request)
    {

        $user = Auth::user();
        $ftp_host = $request->ftp_host;
        $ftp_user_name = $request->ftp_user_name;
        $ftp_password = $request->ftp_password;

        $settings = Setting::where('user_id', $user->id)->first();

        $settings->ftp_host = $ftp_host;
        $settings->ftp_user_name = $ftp_user_name;
        $settings->ftp_password = $ftp_password;

        if (!$settings->update()) {
            //-- Build notification array
            $arrOptions = [
                'message' => trans('messages.option_was_not_updated'),
                'type' => 'error',
                'position' => 'bottomLeft'
            ];
            Session::flash('ftp_change', $arrOptions);
        } else {
            //-- Build notification array
            $arrOptions = [
                'message' => trans('messages.ftp_credentials_were_updated'),
                'type' => 'success',
                'position' => 'topRight'
            ];
            Session::flash('ftp_change', $arrOptions);
        }

        return redirect()->route('office_ftp_connection', ['id' => $user->id]);

    }

    public function updateAdminFtpCredentials(Request $request)
    {

        $strAdminFtpCredentials = $request->admin_frp_credentials;
        $strError = "";
        $result = "success";

        $adminSetting = AdminSetting::findOrFail(Auth::id());
        $adminSetting->use_admin_ftp_credentials = $strAdminFtpCredentials;

        if (!$adminSetting->update()) {
            $result = "";
            $strError = trans('messages.option_was_not_updated');
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));

    }

    public function updateFTPConnectionType(Request $request)
    {

        $strFTPConnectionType = $request->use_sftp;
        $strError = "";
        $result = "success";

        $setting = Setting::findOrFail(Auth::id());
        $setting->ftp_type = $strFTPConnectionType;

        if (!$setting->update()) {
            $result = "";
            $strError = trans('messages.option_was_not_updated');
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));

    }


}
