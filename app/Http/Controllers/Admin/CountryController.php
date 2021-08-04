<?php


namespace App\Http\Controllers\Admin;

class CountryController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\CountryDataTable $dataTable)
    {
        return $dataTable->render("admin.country.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.country.add");
        }
        if ($request->submit) {
            $rules = ["short_name" => "required|unique:country", "long_name" => "required|unique:country", "phone_code" => "required"];
            $niceNames = ["short_name" => "Short Name", "long_name" => "Long Name", "phone_code" => "Phone Code"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $country = new \App\Models\Country();
            $country->short_name = $request->short_name;
            $country->long_name = $request->long_name;
            $country->iso3 = $request->iso3;
            $country->num_code = $request->num_code;
            $country->phone_code = $request->phone_code;
            $country->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/country");
        }
        return redirect(ADMIN_URL . "/country");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Country::find($request->id);
            if (!$data["result"]) {
                abort("404");
            }
            return view("admin.country.edit", $data);
        }
        if ($request->submit) {
            $rules = ["short_name" => "required|unique:country,short_name," . $request->id, "long_name" => "required|unique:country,long_name," . $request->id, "phone_code" => "required"];
            $niceNames = ["short_name" => "Short Name", "long_name" => "Long Name", "phone_code" => "Phone Code"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $country = \App\Models\Country::find($request->id);
            $country->short_name = $request->short_name;
            $country->long_name = $request->long_name;
            $country->iso3 = $request->iso3;
            $country->num_code = $request->num_code;
            $country->phone_code = $request->phone_code;
            $country->save();
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/country");
        }
        return redirect(ADMIN_URL . "/country");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $country_code = \App\Models\Country::find($request->id)->short_name;
        $count = \App\Models\RoomsAddress::where("country", $country_code)->count();
        $reservation_count = \App\Models\Reservation::where("country", $country_code)->count();
        $payout_preferences_count = \App\Models\PayoutPreferences::where("country", $country_code)->count();
        if (0 < $reservation_count) {
            $this->helper->flash_message("error", "Some Reservations have this Country. So, We cannot delete the country.");
        } else {
            if (0 < $payout_preferences_count) {
                $this->helper->flash_message("error", "Some PayoutPreferences have this Country. So, We cannot delete the country.");
            } else {
                if (0 < $count) {
                    $this->helper->flash_message("error", "Rooms have this Country. So, Delete that Rooms or Change that Rooms Country.");
                } else {
                    \App\Models\Country::find($request->id)->delete();
                    $this->helper->flash_message("success", "Deleted Successfully");
                }
            }
        }
        return redirect(ADMIN_URL . "/country");
    }
}

?>