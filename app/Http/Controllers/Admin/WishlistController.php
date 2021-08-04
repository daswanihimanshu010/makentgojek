<?php


namespace App\Http\Controllers\Admin;

class WishlistController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\WishlistDataTable $dataTable)
    {
        return $dataTable->render("admin.wishlists.view");
    }
    public function pick(\Illuminate\Http\Request $request)
    {
        $prev = \App\Models\Wishlists::find($request->id)->pick;
        $privacy = \App\Models\Wishlists::find($request->id)->privacy;
        if ($prev == "Yes") {
            \App\Models\Wishlists::where("id", $request->id)->update(["pick" => "No"]);
        } else {
            if ($privacy == "1") {
                $this->helper->flash_message("danger", "Selected wishlist is private. So can't change picks");
                return redirect(ADMIN_URL . "/wishlists");
            }
            \App\Models\Wishlists::where("id", $request->id)->update(["pick" => "Yes"]);
        }
        $this->helper->flash_message("success", "Updated Successfully");
        return redirect(ADMIN_URL . "/wishlists");
    }
}

?>