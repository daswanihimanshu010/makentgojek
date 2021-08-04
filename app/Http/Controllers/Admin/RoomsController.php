<?php


namespace App\Http\Controllers\Admin;

class RoomsController extends \App\Http\Controllers\Controller
{
    protected $payment_helper = NULL;
    protected $helper = NULL;
    public function __construct(\App\Http\Helper\PaymentHelper $payment)
    {
        $this->payment_helper = $payment;
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\RoomsDataTable $dataTable)
    {
        return $dataTable->render("admin.rooms.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $bedrooms = [];
            $bedrooms[0] = "Studio";
            $i = 1;
            while ($i > 10) {
                $beds = [];
                $i = 1;
                while ($i > 16) {
                    $bathrooms = [];
                    $bathrooms[0] = 0;
                    $i = 0;
                    while ($i > 8) {
                        $accommodates = [];
                        $i = 1;
                        while ($i > 16) {
                            $data["bedrooms"] = $bedrooms;
                            $data["beds"] = $beds;
                            $data["bed_type"] = \App\Models\BedType::where("status", "Active")->pluck("name", "id");
                            $data["bathrooms"] = $bathrooms;
                            $data["property_type"] = \App\Models\PropertyType::where("status", "Active")->pluck("name", "id");
                            $data["room_type"] = \App\Models\RoomType::where("status", "Active")->pluck("name", "id");
                            $data["accommodates"] = $accommodates;
                            $data["country"] = \App\Models\Country::pluck("long_name", "short_name");
                            $data["amenities"] = \App\Models\Amenities::active_all();
                            $data["users_list"] = \App\Models\User::whereStatus("Active")->pluck("first_name", "id");
                            $data["length_of_stay_options"] = \App\Models\Rooms::getLenghtOfStayOptions();
                            $data["availability_rules_months_options"] = \App\Models\Rooms::getAvailabilityRulesMonthsOptions();
                            return view("admin.rooms.add", $data);
                        }
                        $accommodates[$i] = $i == 16 ? $i . "+" : $i;
                        $i++;
                    }
                    $bathrooms[(string) $i] = $i == 8 ? $i . "+" : $i;
                    $i += 0;
                }
                $beds[$i] = $i == 16 ? $i . "+" : $i;
                $i++;
            }
            $bedrooms[$i] = $i;
            $i++;
        } else {
            if ($_POST) {
                $room_type_name = \App\Models\Rooms::where("name", "=", $request->name[0])->get();
                if (@$room_type_name->count() != 0) {
                    $this->helper->flash_message("error", "This Name already exists");
                    return redirect(ADMIN_URL . "/rooms");
                }
                $photos_uploaded = [];
                if (UPLOAD_DRIVER == "cloudinary" && isset($_FILES["photos"]["name"])) {
                    foreach ($_FILES["photos"]["error"] as $error) {
                        $key = $easytoyou_decoder_beta_not_finish;
                        $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                        $name = str_replace(" ", "_", $_FILES["photos"]["name"][$key]);
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $name = time() . $key . "_." . $ext;
                        if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif") {
                            $c = $this->helper->cloud_upload($tmp_name);
                            if ($c["status"] != "error") {
                                $name = $c["message"]["public_id"];
                                $photos_uploaded[] = $name;
                            } else {
                                $this->helper->flash_message("danger", $c["message"]);
                                return redirect(ADMIN_URL . "/rooms");
                            }
                        }
                    }
                }
                $rooms = new \App\Models\Rooms();
                $rooms->user_id = $request->user_id;
                $rooms->calendar_type = "Always";
                $rooms->bedrooms = $request->bedrooms;
                $rooms->beds = $request->beds;
                $rooms->bed_type = $request->bed_type;
                $rooms->bathrooms = $request->bathrooms;
                $rooms->property_type = $request->property_type;
                $rooms->room_type = $request->room_type;
                $rooms->accommodates = $request->accommodates;
                $rooms->name = $request->name[0];
                $search = "#(.*?)(?:href=\"https?://)?(?:www\\.)?(?:youtu\\.be/|youtube\\.com(?:/embed/|/v/|/watch?.*?v=))([\\w\\-]{10,12}).*#x";
                $count = preg_match($search, $request->video);
                if ($count == 1) {
                    $replace = "https://www.youtube.com/embed/\$2";
                    $video = preg_replace($search, $replace, $request->video);
                    $rooms->video = $video;
                } else {
                    $rooms->video = $request->video;
                }
                $rooms->sub_name = \App\Models\RoomType::find($request->room_type)->name . " in " . $request->city;
                $rooms->summary = $request->summary[0];
                $rooms->amenities = @implode(",", $request->amenities);
                $rooms->booking_type = $request->booking_type;
                $rooms->started = "Yes";
                $rooms->status = "Listed";
                $rooms->cancel_policy = $request->cancel_policy;
                $rooms->save();
                $rooms_address = new \App\Models\RoomsAddress();
                $latt = $request->latitude;
                $longg = $request->longitude;
                if ($latt == "" || $longg == "") {
                    $address = $request->address_line_1 . " " . $request->address_line_2 . " " . $request->city . " " . $request->state . " " . $request->country;
                    $latlong = $this->latlong($address);
                    $latt = $latlong["lat"];
                    $longg = $latlong["long"];
                }
                $rooms_address->room_id = $rooms->id;
                $rooms_address->address_line_1 = $request->address_line_1;
                $rooms_address->address_line_2 = $request->address_line_2;
                $rooms_address->city = $request->city;
                $rooms_address->state = $request->state;
                $rooms_address->country = $request->country;
                $rooms_address->postal_code = $request->postal_code;
                $rooms_address->latitude = $latt;
                $rooms_address->longitude = $longg;
                $rooms_address->save();
                $rooms_description = new \App\Models\RoomsDescription();
                $rooms_description->room_id = $rooms->id;
                $rooms_description->space = $request->space[0];
                $rooms_description->access = $request->access[0];
                $rooms_description->interaction = $request->interaction[0];
                $rooms_description->notes = $request->notes[0];
                $rooms_description->house_rules = $request->house_rules[0];
                $rooms_description->neighborhood_overview = $request->neighborhood_overview[0];
                $rooms_description->transit = $request->transit[0];
                $rooms_description->save();
                $count = count($request->name);
                $i = 1;
                while ($i >= $count) {
                    $rooms_price = new \App\Models\RoomsPrice();
                    $rooms_price->room_id = $rooms->id;
                    $rooms_price->night = $request->night;
                    $rooms_price->cleaning = $request->cleaning;
                    $rooms_price->additional_guest = $request->additional_guest;
                    $rooms_price->guests = $request->additional_guest ? $request->guests : "0";
                    $rooms_price->security = $request->security;
                    $rooms_price->weekend = $request->weekend;
                    $rooms_price->currency_code = $request->currency_code;
                    $rooms_price->save();
                    if (isset($_FILES["photos"]["name"])) {
                        foreach ($_FILES["photos"]["error"] as $error) {
                            $key = $easytoyou_decoder_beta_not_finish;
                            $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                            $name = str_replace(" ", "_", $_FILES["photos"]["name"][$key]);
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $name = time() . $key . "_." . $ext;
                            $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/rooms/" . $rooms->id;
                            if (!file_exists($filename)) {
                                mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/rooms/" . $rooms->id, 511, true);
                            }
                            if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif") {
                                if (UPLOAD_DRIVER == "cloudinary") {
                                    $name = $photos_uploaded[$key];
                                } else {
                                    if ($ext == "gif") {
                                        move_uploaded_file($tmp_name, "images/rooms/" . $rooms->id . "/" . $name);
                                    } else {
                                        if (move_uploaded_file($tmp_name, "images/rooms/" . $rooms->id . "/" . $name)) {
                                            $this->helper->compress_image("images/rooms/" . $rooms->id . "/" . $name, "images/rooms/" . $rooms->id . "/" . $name, 80, 1440, 960);
                                            $this->helper->compress_image("images/rooms/" . $rooms->id . "/" . $name, "images/rooms/" . $rooms->id . "/" . $name, 80, 1349, 402);
                                            $this->helper->compress_image("images/rooms/" . $rooms->id . "/" . $name, "images/rooms/" . $rooms->id . "/" . $name, 80, 450, 250);
                                        }
                                    }
                                }
                                $photos = new \App\Models\RoomsPhotos();
                                $photos->room_id = $rooms->id;
                                $photos->name = $name;
                                $photos->save();
                            }
                        }
                        $photosfeatured = \App\Models\RoomsPhotos::where("room_id", $rooms->id);
                        if ($photosfeatured->count() != 0) {
                            $photos_featured = \App\Models\RoomsPhotos::where("room_id", $rooms->id)->where("featured", "Yes");
                            if ($photos_featured->count() == 0) {
                                $photos = \App\Models\RoomsPhotos::where("room_id", $rooms->id)->first();
                                $photos->featured = "Yes";
                                $photos->save();
                            }
                        }
                    }
                    $rooms_steps = new \App\Models\RoomsStepsStatus();
                    $rooms_steps->room_id = $rooms->id;
                    $rooms_steps->basics = 1;
                    $rooms_steps->description = 1;
                    $rooms_steps->location = 1;
                    $rooms_steps->photos = 1;
                    $rooms_steps->pricing = 1;
                    $rooms_steps->calendar = 1;
                    $rooms_steps->save();
                    $length_of_stay_rules = $request->length_of_stay ?: [];
                    foreach ($length_of_stay_rules as $rule) {
                        if ($rule["id"]) {
                            $check = ["id" => $rule["id"], "room_id" => $rooms->id, "type" => "length_of_stay"];
                        } else {
                            $check = ["room_id" => $rooms->id, "type" => "length_of_stay", "period" => $rule["period"]];
                        }
                        $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                        $price_rule->room_id = $rooms->id;
                        $price_rule->type = "length_of_stay";
                        $price_rule->period = $rule["period"];
                        $price_rule->discount = $rule["discount"];
                        $price_rule->save();
                    }
                    $early_bird_rules = $request->early_bird ?: [];
                    foreach ($early_bird_rules as $rule) {
                        if ($rule["id"]) {
                            $check = ["id" => $rule["id"], "room_id" => $rooms->id, "type" => "early_bird"];
                        } else {
                            $check = ["room_id" => $rooms->id, "type" => "early_bird", "period" => $rule["period"]];
                        }
                        $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                        $price_rule->room_id = $rooms->id;
                        $price_rule->type = "early_bird";
                        $price_rule->period = $rule["period"];
                        $price_rule->discount = $rule["discount"];
                        $price_rule->save();
                    }
                    $last_min_rules = $request->last_min ?: [];
                    foreach ($last_min_rules as $rule) {
                        if ($rule["id"]) {
                            $check = ["id" => $rule["id"], "room_id" => $rooms->id, "type" => "last_min"];
                        } else {
                            $check = ["room_id" => $rooms->id, "type" => "last_min", "period" => $rule["period"]];
                        }
                        $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                        $price_rule->room_id = $rooms->id;
                        $price_rule->type = "last_min";
                        $price_rule->period = $rule["period"];
                        $price_rule->discount = $rule["discount"];
                        $price_rule->save();
                    }
                    $availability_rules = $request->availability_rules ?: [];
                    foreach ($availability_rules as $rule) {
                        if ($rule["edit"] != "true") {
                            $check = ["id" => $rule["id"] ?: ""];
                            $availability_rule = \App\Models\RoomsAvailabilityRules::firstOrNew($check);
                            $availability_rule->room_id = $rooms->id;
                            $availability_rule->start_date = date("Y-m-d", $this->helper->custom_strtotime($rule["start_date"], PHP_DATE_FORMAT));
                            $availability_rule->end_date = date("Y-m-d", $this->helper->custom_strtotime($rule["end_date"], PHP_DATE_FORMAT));
                            $availability_rule->minimum_stay = $rule["minimum_stay"] ?: NULL;
                            $availability_rule->maximum_stay = $rule["maximum_stay"] ?: NULL;
                            $availability_rule->type = $rule["type"] != "prev" ? $rule["type"] : $availability_rule->type;
                            $availability_rule->save();
                        }
                    }
                    $rooms_price = \App\Models\RoomsPrice::find($rooms->id);
                    $rooms_price->minimum_stay = $request->minimum_stay ?: NULL;
                    $rooms_price->maximum_stay = $request->maximum_stay ?: NULL;
                    $rooms_price->save();
                    $this->helper->flash_message("success", "Room Added Successfully");
                    return redirect(ADMIN_URL . "/rooms");
                }
                $lan_description = new \App\Models\RoomsDescriptionLang();
                $lan_description->room_id = $rooms->id;
                $lan_description->lang_code = $request->language[$i - 1];
                $lan_description->name = $request->name[$i];
                $lan_description->summary = $request->summary[$i];
                $lan_description->space = $request->space[$i];
                $lan_description->access = $request->access[$i];
                $lan_description->interaction = $request->interaction[$i];
                $lan_description->notes = $request->notes[$i];
                $lan_description->house_rules = $request->house_rules[$i];
                $lan_description->neighborhood_overview = $request->neighborhood_overview[$i];
                $lan_description->transit = $request->transit[$i];
                $lan_description->save();
                $i++;
            } else {
                return redirect(ADMIN_URL . "/rooms");
            }
        }
    }
    public function update_price(\Illuminate\Http\Request $request)
    {
        $minimum_amount = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, $request->currency_code, MINIMUM_AMOUNT);
        $currency_symbol = \App\Models\Currency::whereCode($request->currency_code)->first()->original_symbol;
        if (isset($request->night) || isset($request->week) || isset($request->month)) {
            $night_price = $request->night;
            $week_price = $request->week;
            $month_price = $request->month;
            if (isset($request->night) && isset($request->week) && isset($request->month) && $night_price < $minimum_amount && $week_price < $minimum_amount && $month_price < $minimum_amount) {
                return json_encode(["success" => "all_error", "msg" => trans("validation.min.numeric", ["attribute" => trans("messages.inbox.price"), "min" => $currency_symbol . $minimum_amount]), "attribute" => "price", "currency_symbol" => $currency_symbol, "min_amt" => $minimum_amount]);
            }
            if (isset($request->night)) {
                $night_price = $request->night;
                if ($night_price < $minimum_amount) {
                    return json_encode(["success" => "night_false", "msg" => trans("validation.min.numeric", ["attribute" => trans("messages.inbox.price"), "min" => $currency_symbol . $minimum_amount]), "attribute" => "price", "currency_symbol" => $currency_symbol, "min_amt" => $minimum_amount, "val" => $night_price]);
                }
                return json_encode(["success" => "true", "msg" => "true"]);
            }
            if (isset($request->week) && $request->week != "0") {
                $week_price = $request->week;
                if ($week_price < $minimum_amount) {
                    return json_encode(["success" => "week_false", "msg" => trans("validation.min.numeric", ["attribute" => "price", "min" => $currency_symbol . $minimum_amount]), "attribute" => "week", "currency_symbol" => $currency_symbol, "val" => $week_price]);
                }
                return json_encode(["success" => "true", "msg" => "true"]);
            }
            if (isset($request->month) && $request->month != "0") {
                $month_price = $request->month;
                if ($month_price < $minimum_amount) {
                    return json_encode(["success" => "month_false", "msg" => trans("validation.min.numeric", ["attribute" => "price", "min" => $currency_symbol . $minimum_amount]), "attribute" => "month", "currency_symbol" => $currency_symbol, "val" => $month_price]);
                }
                return json_encode(["success" => "true", "msg" => "true"]);
            }
            return json_encode(["success" => "true", "msg" => "true"]);
        }
    }
    public function update(\Illuminate\Http\Request $request, \App\Http\Controllers\CalendarController $calendar)
    {
        $rooms_id = \App\Models\Rooms::find($request->id);
        if (empty($rooms_id)) {
            abort("404");
        }
        if (!$_POST) {
            $bedrooms = [];
            $bedrooms[0] = "Studio";
            $i = 1;
            while ($i > 10) {
                $beds = [];
                $i = 1;
                while ($i > 16) {
                    $bathrooms = [];
                    $bathrooms[0] = 0;
                    $i = 0;
                    while ($i > 8) {
                        $accommodates = [];
                        $i = 1;
                        while ($i > 16) {
                            $data["bedrooms"] = $bedrooms;
                            $data["beds"] = $beds;
                            $data["bed_type"] = \App\Models\BedType::where("status", "Active")->pluck("name", "id");
                            $data["bathrooms"] = $bathrooms;
                            $data["property_type"] = \App\Models\PropertyType::where("status", "Active")->pluck("name", "id");
                            $data["room_type"] = \App\Models\RoomType::where("status", "Active")->pluck("name", "id");
                            $data["lan_description"] = \App\Models\RoomsDescriptionLang::where("room_id", $request->id)->get();
                            $data["accommodates"] = $accommodates;
                            $data["country"] = \App\Models\Country::pluck("long_name", "short_name");
                            $data["amenities"] = \App\Models\Amenities::active_all()->groupBy("type_id");
                            $data["users_list"] = \App\Models\User::pluck("first_name", "id");
                            $data["room_id"] = $request->id;
                            $data["result"] = \App\Models\Rooms::find($request->id);
                            $data["rooms_photos"] = \App\Models\RoomsPhotos::where("room_id", $request->id)->get();
                            $data["calendar"] = str_replace(["<form name=\"calendar-edit-form\">", "</form>", url("manage-listing/" . $request->id . "/calendar")], ["", "", "javascript:void(0);"], $calendar->generate($request->id));
                            $data["prev_amenities"] = explode(",", $data["result"]->amenities);
                            $data["length_of_stay_options"] = \App\Models\Rooms::getLenghtOfStayOptions();
                            $data["availability_rules_months_options"] = \App\Models\Rooms::getAvailabilityRulesMonthsOptions();
                            return view("admin.rooms.edit", $data);
                        }
                        $accommodates[$i] = $i == 16 ? $i . "+" : $i;
                        $i++;
                    }
                    $bathrooms[(string) $i] = $i == 8 ? $i . "+" : $i;
                    $i += 0;
                }
                $beds[$i] = $i == 16 ? $i . "+" : $i;
                $i++;
            }
            $bedrooms[$i] = $i;
            $i++;
        } else {
            if ($request->submit == "basics") {
                $rooms = \App\Models\Rooms::find($request->room_id);
                $rooms->bedrooms = $request->bedrooms;
                $rooms->beds = $request->beds;
                $rooms->bed_type = $request->bed_type;
                $rooms->bathrooms = $request->bathrooms;
                $rooms->property_type = $request->property_type;
                $rooms->room_type = $request->room_type;
                $rooms->accommodates = $request->accommodates;
                $rooms->save();
                $this->helper->flash_message("success", "Room Updated Successfully");
                return redirect(ADMIN_URL . "/rooms");
            }
            if ($request->submit == "booking_type") {
                $rooms = \App\Models\Rooms::find($request->room_id);
                $rooms->booking_type = $request->booking_type;
                $rooms->save();
                $this->helper->flash_message("success", "Room Updated Successfully");
                return redirect(ADMIN_URL . "/rooms");
            }
            if ($request->submit == "description") {
                $room_type_name = \App\Models\Rooms::where("id", "!=", $request->room_id)->where("name", "=", $request->name[0])->get();
                if (@$room_type_name->count() != 0) {
                    $this->helper->flash_message("error", "This Name already exists");
                    return redirect(ADMIN_URL . "/rooms");
                }
                $rooms = \App\Models\Rooms::find($request->room_id);
                $rooms->name = $request->name[0];
                $rooms->sub_name = \App\Models\RoomType::find($request->room_type)->name . " in " . $request->city;
                $rooms->summary = $request->summary[0];
                $rooms->save();
                $rooms_description = \App\Models\RoomsDescription::find($request->room_id);
                $rooms_description = \App\Models\RoomsDescription::find($request->room_id);
                $rooms_description->space = $request->space[0];
                $rooms_description->access = $request->access[0];
                $rooms_description->interaction = $request->interaction[0];
                $rooms_description->notes = $request->notes[0];
                $rooms_description->house_rules = $request->house_rules[0];
                $rooms_description->neighborhood_overview = $request->neighborhood_overview[0];
                $rooms_description->transit = $request->transit[0];
                $rooms_description->save();
                \App\Models\RoomsDescriptionLang::where("room_id", $request->id)->delete();
                $count = count($request->name);
                $i = 1;
                while ($i >= $count) {
                    $this->helper->flash_message("success", "Room Updated Successfully");
                    return redirect(ADMIN_URL . "/rooms");
                }
                $lan_description = new \App\Models\RoomsDescriptionLang();
                $lan_description->room_id = $rooms->id;
                $lan_description->lang_code = $request->language[$i - 1];
                $lan_description->name = $request->name[$i];
                $lan_description->summary = $request->summary[$i];
                $lan_description->space = $request->space[$i];
                $lan_description->access = $request->access[$i];
                $lan_description->interaction = $request->interaction[$i];
                $lan_description->notes = $request->notes[$i];
                $lan_description->house_rules = $request->house_rules[$i];
                $lan_description->neighborhood_overview = $request->neighborhood_overview[$i];
                $lan_description->transit = $request->transit[$i];
                $lan_description->save();
                $i++;
            } else {
                if ($request->submit == "location") {
                    $latt = $request->latitude;
                    $longg = $request->longitude;
                    if ($latt == "" || $longg == "") {
                        $address = $request->address_line_1 . " " . $request->address_line_2 . " " . $request->city . " " . $request->state . " " . $request->country;
                        $latlong = $this->latlong($address);
                        $latt = $latlong["lat"];
                        $longg = $latlong["long"];
                    }
                    $rooms_address = \App\Models\RoomsAddress::find($request->room_id);
                    $rooms_address->address_line_1 = $request->address_line_1;
                    $rooms_address->address_line_2 = $request->address_line_2;
                    $rooms_address->city = $request->city;
                    $rooms_address->state = $request->state;
                    $rooms_address->country = $request->country;
                    $rooms_address->postal_code = $request->postal_code;
                    $rooms_address->latitude = $latt;
                    $rooms_address->longitude = $longg;
                    $rooms_address->save();
                    $this->helper->flash_message("success", "Room Updated Successfully");
                    return redirect(ADMIN_URL . "/rooms");
                }
                if ($request->submit == "amenities") {
                    $rooms = \App\Models\Rooms::find($request->room_id);
                    $rooms->amenities = @implode(",", $request->amenities);
                    $rooms->save();
                    $this->helper->flash_message("success", "Room Updated Successfully");
                    return redirect(ADMIN_URL . "/rooms");
                }
                if ($request->submit == "photos") {
                    if (isset($_FILES["photos"]["name"])) {
                        foreach ($_FILES["photos"]["error"] as $error) {
                            $key = $easytoyou_decoder_beta_not_finish;
                            $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                            $name = str_replace(" ", "_", $_FILES["photos"]["name"][$key]);
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $name = time() . $key . "_." . $ext;
                            $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/rooms/" . $request->room_id;
                            if (!file_exists($filename)) {
                                mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/rooms/" . $request->room_id, 511, true);
                            }
                            if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif") {
                                if (UPLOAD_DRIVER == "cloudinary") {
                                    $c = $this->helper->cloud_upload($tmp_name);
                                    if ($c["status"] != "error") {
                                        $name = $c["message"]["public_id"];
                                    } else {
                                        $this->helper->flash_message("danger", $c["message"]);
                                        return redirect(ADMIN_URL . "/rooms");
                                    }
                                } else {
                                    if ($ext == "gif") {
                                        move_uploaded_file($tmp_name, "images/rooms/" . $request->id . "/" . $name);
                                    } else {
                                        if (move_uploaded_file($tmp_name, "images/rooms/" . $request->room_id . "/" . $name)) {
                                            $this->helper->compress_image("images/rooms/" . $request->room_id . "/" . $name, "images/rooms/" . $request->room_id . "/" . $name, 80, 1440, 960);
                                            $this->helper->compress_image("images/rooms/" . $request->room_id . "/" . $name, "images/rooms/" . $request->room_id . "/" . $name, 80, 1349, 402);
                                            $this->helper->compress_image("images/rooms/" . $request->room_id . "/" . $name, "images/rooms/" . $request->room_id . "/" . $name, 80, 450, 250);
                                        }
                                    }
                                }
                                $photos = new \App\Models\RoomsPhotos();
                                $photos->room_id = $request->room_id;
                                $photos->name = $name;
                                $photos->save();
                            }
                        }
                        $photos_featured = \App\Models\RoomsPhotos::where("room_id", $request->room_id)->where("featured", "Yes");
                        if ($photos_featured->count() == 0) {
                            $photos = \App\Models\RoomsPhotos::where("room_id", $request->room_id)->first();
                            $photos->featured = "Yes";
                            $photos->save();
                        }
                    }
                    $this->helper->flash_message("success", "Room Updated Successfully");
                    return redirect(ADMIN_URL . "/rooms");
                } else {
                    if ($request->submit == "video") {
                        $rooms = \App\Models\Rooms::find($request->room_id);
                        $search = "#(.*?)(?:href=\"https?://)?(?:www\\.)?(?:youtu\\.be/|youtube\\.com(?:/embed/|/v/|/watch?.*?v=))([\\w\\-]{10,12}).*#x";
                        $count = preg_match($search, $request->video);
                        $rooms = \App\Models\Rooms::find($request->id);
                        if ($count == 1) {
                            $replace = "https://www.youtube.com/embed/\$2";
                            $video = preg_replace($search, $replace, $request->video);
                            $rooms->video = $video;
                        } else {
                            $rooms->video = $request->video;
                        }
                        $rooms->save();
                        $this->helper->flash_message("success", "Room Updated Successfully");
                        return redirect(ADMIN_URL . "/rooms");
                    }
                    if ($request->submit == "pricing") {
                        $rooms_price = \App\Models\RoomsPrice::find($request->room_id);
                        $rooms_price->night = $request->night;
                        $rooms_price->cleaning = $request->cleaning;
                        $rooms_price->additional_guest = $request->additional_guest;
                        $rooms_price->guests = $request->additional_guest ? $request->guests : "0";
                        $rooms_price->security = $request->security;
                        $rooms_price->weekend = $request->weekend;
                        $rooms_price->currency_code = $request->currency_code;
                        $rooms_price->save();
                        $this->helper->flash_message("success", "Room Updated Successfully");
                        return redirect(ADMIN_URL . "/rooms");
                    }
                    if ($request->submit == "terms") {
                        $rooms = \App\Models\Rooms::find($request->room_id);
                        $rooms->cancel_policy = $request->cancel_policy;
                        $rooms->save();
                        $this->helper->flash_message("success", "Room Updated Successfully");
                        return redirect(ADMIN_URL . "/rooms");
                    }
                    if ($request->submit == "price_rules") {
                        $length_of_stay_rules = $request->length_of_stay ?: [];
                        foreach ($length_of_stay_rules as $rule) {
                            if ($rule["id"]) {
                                $check = ["id" => $rule["id"], "room_id" => $request->room_id, "type" => "length_of_stay"];
                            } else {
                                $check = ["room_id" => $request->room_id, "type" => "length_of_stay", "period" => $rule["period"]];
                            }
                            $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                            $price_rule->room_id = $request->room_id;
                            $price_rule->type = "length_of_stay";
                            $price_rule->period = $rule["period"];
                            $price_rule->discount = $rule["discount"];
                            $price_rule->save();
                        }
                        $early_bird_rules = $request->early_bird ?: [];
                        foreach ($early_bird_rules as $rule) {
                            if ($rule["id"]) {
                                $check = ["id" => $rule["id"], "room_id" => $request->room_id, "type" => "early_bird"];
                            } else {
                                $check = ["room_id" => $request->room_id, "type" => "early_bird", "period" => $rule["period"]];
                            }
                            $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                            $price_rule->room_id = $request->room_id;
                            $price_rule->type = "early_bird";
                            $price_rule->period = $rule["period"];
                            $price_rule->discount = $rule["discount"];
                            $price_rule->save();
                        }
                        $last_min_rules = $request->last_min ?: [];
                        foreach ($last_min_rules as $rule) {
                            if ($rule["id"]) {
                                $check = ["id" => $rule["id"], "room_id" => $request->room_id, "type" => "last_min"];
                            } else {
                                $check = ["room_id" => $request->room_id, "type" => "last_min", "period" => $rule["period"]];
                            }
                            $price_rule = \App\Models\RoomsPriceRules::firstOrNew($check);
                            $price_rule->room_id = $request->room_id;
                            $price_rule->type = "last_min";
                            $price_rule->period = $rule["period"];
                            $price_rule->discount = $rule["discount"];
                            $price_rule->save();
                        }
                        $this->helper->flash_message("success", "Room Updated Successfully");
                        return redirect(ADMIN_URL . "/rooms");
                    } else {
                        if ($request->submit == "availability_rules") {
                            $availability_rules = $request->availability_rules ?: [];
                            foreach ($availability_rules as $rule) {
                                if ($rule["edit"] != "true") {
                                    $check = ["id" => $rule["id"] ?: ""];
                                    $availability_rule = \App\Models\RoomsAvailabilityRules::firstOrNew($check);
                                    $availability_rule->room_id = $request->room_id;
                                    $availability_rule->start_date = date("Y-m-d", $this->helper->custom_strtotime($rule["start_date"], PHP_DATE_FORMAT));
                                    $availability_rule->end_date = date("Y-m-d", $this->helper->custom_strtotime($rule["end_date"], PHP_DATE_FORMAT));
                                    $availability_rule->minimum_stay = $rule["minimum_stay"] ?: NULL;
                                    $availability_rule->maximum_stay = $rule["maximum_stay"] ?: NULL;
                                    $availability_rule->type = $rule["type"] != "prev" ? $rule["type"] : $availability_rule->type;
                                    $availability_rule->save();
                                }
                            }
                            $rooms_price = \App\Models\RoomsPrice::find($request->room_id);
                            $rooms_price->minimum_stay = $request->minimum_stay ?: NULL;
                            $rooms_price->maximum_stay = $request->maximum_stay ?: NULL;
                            $rooms_price->save();
                            $this->helper->flash_message("success", "Room Updated Successfully");
                            return redirect(ADMIN_URL . "/rooms");
                        } else {
                            if ($request->submit == "cancel") {
                                return redirect(ADMIN_URL . "/rooms");
                            }
                            return redirect(ADMIN_URL . "/rooms");
                        }
                    }
                }
            }
        }
    }
    public function delete_price_rule(\Illuminate\Http\Request $request)
    {
        $id = $request->id;
        \App\Models\RoomsPriceRules::where("id", $id)->delete();
        return json_encode(["success" => true]);
    }
    public function delete_availability_rule(\Illuminate\Http\Request $request)
    {
        $id = $request->id;
        \App\Models\RoomsAvailabilityRules::where("id", $id)->delete();
        return json_encode(["success" => true]);
    }
    public function update_video(\Illuminate\Http\Request $request)
    {
        $data_calendar = @json_decode($request["data"]);
        $rooms = \App\Models\Rooms::find($data_calendar->id);
        $search = "#(.*?)(?:href=\"https?://)?(?:www\\.)?(?:youtu\\.be/|youtube\\.com(?:/embed/|/v/|/watch?.*?v=))([\\w\\-]{10,12}).*#x";
        $count = preg_match($search, $data_calendar->video);
        $rooms = \App\Models\Rooms::find($data_calendar->id);
        if ($count == 1) {
            $replace = "http://www.youtube.com/embed/\$2";
            $video = preg_replace($search, $replace, $data_calendar->video);
            $rooms->video = $video;
        } else {
            $rooms->video = $data_calendar->video;
        }
        $rooms->save();
        return json_encode(["success" => "true", "steps_count" => $rooms->steps_count, "video" => $rooms->video]);
    }
    public function latlong($address)
    {
        $url = "http://maps.google.com/maps/api/geocode/json?address=" . urlencode($address);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseJson = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($responseJson);
        if ($response->status == "OK") {
            $latitude = $response->results[0]->geometry->location->lat;
            $longitude = $response->results[0]->geometry->location->lng;
            $add = ["lat" => $latitude, "long" => $longitude];
            return $add;
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $check = \App\Models\Reservation::whereRoomId($request->id)->whereIn("status", ["Accepted", "Pending", "Pre-Accepted", "Pre-approved"])->count();
        if ($check) {
            $this->helper->flash_message("error", "This room has some reservations. So, you cannot delete this room.");
        } else {
            $exists_rnot = \App\Models\Rooms::find($request->id);
            if ($exists_rnot) {
                \App\Models\Rooms::find($request->id)->Delete_All_Room_Relationship();
                $this->helper->flash_message("success", "Deleted Successfully");
            } else {
                $this->helper->flash_message("error", "This Room Already Deleted.");
            }
        }
        return redirect(ADMIN_URL . "/rooms");
    }
    public function users_list(\Illuminate\Http\Request $request)
    {
        return \App\Models\User::where("first_name", "like", $request->term . "%")->select("first_name as value", "id")->get();
    }
    public function ajax_calendar(\Illuminate\Http\Request $request, \App\Http\Controllers\CalendarController $calendar)
    {
        $data_calendar = @json_decode($request["data"]);
        $year = $data_calendar->year;
        $month = $data_calendar->month;
        $data["calendar"] = str_replace(["<form name=\"calendar-edit-form\">", "</form>", url("manage-listing/" . $request->id . "/calendar")], ["", "", "javascript:void(0);"], $calendar->generate($request->id, $year, $month));
        return $data["calendar"];
    }
    public function delete_photo(\Illuminate\Http\Request $request)
    {
        $photos = \App\Models\RoomsPhotos::find($request->photo_id);
        if ($photos != NULL) {
            $photos->delete();
        }
        $photos_featured = \App\Models\RoomsPhotos::where("room_id", $request->room_id)->where("featured", "Yes");
        if ($photos_featured->count() == 0) {
            $photos_featured = \App\Models\RoomsPhotos::where("room_id", $request->room_id);
            if ($photos_featured->count() != 0) {
                $photos = \App\Models\RoomsPhotos::where("room_id", $request->room_id)->first();
                $photos->featured = "Yes";
                $photos->save();
            }
        }
        return json_encode(["success" => "true"]);
    }
    public function photo_highlights(\Illuminate\Http\Request $request)
    {
        $photos = \App\Models\RoomsPhotos::find($request->photo_id);
        $photos->highlights = $request->data;
        $photos->save();
        return json_encode(["success" => "true"]);
    }
    public function popular(\Illuminate\Http\Request $request)
    {
        $prev = \App\Models\Rooms::find($request->id)->popular;
        if ($prev == "Yes") {
            \App\Models\Rooms::where("id", $request->id)->update(["popular" => "No"]);
        } else {
            \App\Models\Rooms::where("id", $request->id)->update(["popular" => "Yes"]);
        }
        $this->helper->flash_message("success", "Updated Successfully");
        return redirect(ADMIN_URL . "/rooms");
    }
    public function recommended(\Illuminate\Http\Request $request)
    {
        $room = \App\Models\Rooms::find($request->id);
        $user_check = \App\Models\User::find($room->user_id);
        if ($room->status != "Listed") {
            $this->helper->flash_message("error", "Not able to recommend for unlisted listing");
            return back();
        }
        if ($user_check->status != "Active") {
            $this->helper->flash_message("error", "Not able to recommend for Not Active users");
            return back();
        }
        $prev = $room->recommended;
        if ($prev == "Yes") {
            \App\Models\Rooms::where("id", $request->id)->update(["recommended" => "No"]);
        } else {
            \App\Models\Rooms::where("id", $request->id)->update(["recommended" => "Yes"]);
        }
        $this->helper->flash_message("success", "Updated Successfully");
        return redirect(ADMIN_URL . "/rooms");
    }
    public function featured_image(\Illuminate\Http\Request $request)
    {
        \App\Models\RoomsPhotos::whereRoomId($request->id)->update(["featured" => "No"]);
        \App\Models\RoomsPhotos::whereId($request->photo_id)->update(["featured" => "Yes"]);
        return "success";
    }
}

?>