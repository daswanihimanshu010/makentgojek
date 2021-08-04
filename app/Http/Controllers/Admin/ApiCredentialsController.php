<?php


namespace App\Http\Controllers\Admin;

class ApiCredentialsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\ApiCredentials::get();
            return view("admin.api_credentials", $data);
        }
        if ($request->submit) {
            $rules = ["facebook_client_id" => "required", "facebook_client_secret" => "required", "google_client_id" => "required", "google_client_secret" => "required", "google_map_key" => "required", "google_map_server_key" => "required", "linkedin_client_id" => "required", "linkedin_client_secret" => "required", "nexmo_api" => "required", "nexmo_secret" => "required", "nexmo_from" => "required", "cloud_name" => "required", "cloud_key" => "required", "cloud_secret" => "required", "cloud_base_url" => "required", "cloud_secure_url" => "required", "cloud_api_url" => "required"];
            $niceNames = ["facebook_client_id" => "Facebook Client ID", "facebook_client_secret" => "Facebook Client Secret", "google_client_id" => "Google Client ID", "google_client_secret" => "Google Client Secret", "google_map_key" => "Google Map Browser Key", "google_map_server_key" => "Google Map Server Key", "linkedin_client_id" => "LinkedIn Client ID", "linkedin_client_secret" => "LinkedIn Client Secret", "cloud_name" => "Cloudinary Name", "cloud_key" => "Cloudinary Key", "cloud_secret" => "Cloudinary Secret", "cloud_base_url" => "Cloudinary BaseUrl", "cloud_secure_url" => "Cloudinary SecureUrl", "cloud_api_url" => "Cloudinary ApiUrl"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\ApiCredentials::where(["name" => "client_id", "site" => "Facebook"])->update(["value" => $request->facebook_client_id]);
            \App\Models\ApiCredentials::where(["name" => "client_secret", "site" => "Facebook"])->update(["value" => $request->facebook_client_secret]);
            \App\Models\ApiCredentials::where(["name" => "client_id", "site" => "Google"])->update(["value" => $request->google_client_id]);
            \App\Models\ApiCredentials::where(["name" => "client_secret", "site" => "Google"])->update(["value" => $request->google_client_secret]);
            \App\Models\ApiCredentials::where(["name" => "key", "site" => "GoogleMap"])->update(["value" => $request->google_map_key]);
            \App\Models\ApiCredentials::where(["name" => "server_key", "site" => "GoogleMap"])->update(["value" => $request->google_map_server_key]);
            \App\Models\ApiCredentials::where(["name" => "client_id", "site" => "LinkedIn"])->update(["value" => $request->linkedin_client_id]);
            \App\Models\ApiCredentials::where(["name" => "client_secret", "site" => "LinkedIn"])->update(["value" => $request->linkedin_client_secret]);
            \App\Models\ApiCredentials::where(["name" => "key", "site" => "Nexmo"])->update(["value" => $request->nexmo_api]);
            \App\Models\ApiCredentials::where(["name" => "secret", "site" => "Nexmo"])->update(["value" => $request->nexmo_secret]);
            \App\Models\ApiCredentials::where(["name" => "from", "site" => "Nexmo"])->update(["value" => $request->nexmo_from]);
            \App\Models\ApiCredentials::where(["name" => "cloudinary_name", "site" => "Cloudinary"])->update(["value" => $request->cloud_name]);
            \App\Models\ApiCredentials::where(["name" => "cloudinary_key", "site" => "Cloudinary"])->update(["value" => $request->cloud_key]);
            \App\Models\ApiCredentials::where(["name" => "cloudinary_secret", "site" => "Cloudinary"])->update(["value" => $request->cloud_secret]);
            \App\Models\ApiCredentials::where(["name" => "cloud_base_url", "site" => "Cloudinary"])->update(["value" => $request->cloud_base_url]);
            \App\Models\ApiCredentials::where(["name" => "cloud_secure_url", "site" => "Cloudinary"])->update(["value" => $request->cloud_secure_url]);
            \App\Models\ApiCredentials::where(["name" => "cloud_api_url", "site" => "Cloudinary"])->update(["value" => $request->cloud_api_url]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/api_credentials");
        }
        return redirect(ADMIN_URL . "/api_credentials");
    }
}

?>