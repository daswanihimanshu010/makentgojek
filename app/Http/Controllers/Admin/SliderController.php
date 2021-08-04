<?php


namespace App\Http\Controllers\Admin;

class SliderController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\SliderDataTable $dataTable)
    {
        return $dataTable->render("admin.slider.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.slider.add");
        }
        if ($request->submit) {
            $rules = ["image" => "required|mimes:jpg,png,gif,jpeg", "order" => "required", "status" => "required", "front_end" => "required"];
            $niceNames = ["image" => "Image", "order" => "Position", "status" => "Status", "front_end" => "Slider For"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $image = $request->file("image");
            $filesize = $image->getSize();
            if (5242880 < $filesize) {
                $this->helper->flash_message("error", trans("messages.profile.image_size_exceeds_5mb"));
                return back();
            }
            if (UPLOAD_DRIVER == "cloudinary") {
                $c = $this->helper->cloud_upload($request->file("image"));
                if ($c["status"] != "error") {
                    $filename = $c["message"]["public_id"];
                } else {
                    $this->helper->flash_message("danger", $c["message"]);
                    return redirect(ADMIN_URL . "/slider");
                }
            } else {
                $extension = $image->getClientOriginalExtension();
                $filename = "slider_" . time() . "." . $extension;
                $success = $image->move("images/slider", $filename);
                $this->helper->compress_image("images/slider/" . $filename, "images/slider/" . $filename, 80);
                if (!$success) {
                    return back()->withError("Could not upload Image");
                }
            }
            $slider = new \App\Models\Slider();
            $slider->image = $filename;
            $slider->order = $request->order;
            $slider->status = $request->status;
            $slider->front_end = $request->front_end;
            $slider->created_at = date("Y-m-d H:i:s");
            $slider->updated_at = date("Y-m-d H:i:s");
            $slider->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/slider");
        }
        return redirect(ADMIN_URL . "/slider");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Slider::find($request->id);
            return view("admin.slider.edit", $data);
        }
        if ($request->submit) {
            $rules = ["image" => "mimes:jpeg,png,gif", "order" => "required", "status" => "required", "front_end" => "required"];
            $niceNames = ["order" => "Position", "status" => "Status", "image" => "Image", "front_end" => "Slider For"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $slider = \App\Models\Slider::find($request->id);
            $image = $request->file("image");
            if ($image) {
                $filesize = $image->getSize();
                if (5242880 < $filesize) {
                    $this->helper->flash_message("error", trans("messages.profile.image_size_exceeds_5mb"));
                    return back();
                }
                if (UPLOAD_DRIVER == "cloudinary") {
                    $c = $this->helper->cloud_upload($request->file("image"));
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect(ADMIN_URL . "/slider");
                    }
                } else {
                    $extension = $image->getClientOriginalExtension();
                    $filename = "slider_" . time() . "." . $extension;
                    $success = $image->move("images/slider", $filename);
                    $this->helper->compress_image("images/slider/" . $filename, "images/slider/" . $filename, 80);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                $slider->image = $filename;
            }
            $slider->order = $request->order;
            $slider->status = $request->status;
            $slider->front_end = $request->front_end;
            $slider->updated_at = date("Y-m-d H:i:s");
            $slider->save();
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/slider");
        }
        return redirect(ADMIN_URL . "/slider");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $slider = \App\Models\Slider::find($request->id);
        if ($slider != "") {
            $slider->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/slider");
    }
}

?>