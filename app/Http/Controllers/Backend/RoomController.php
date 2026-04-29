<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Room;
use App\Models\MultiImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\RoomNumber;

class RoomController extends Controller
{
    // Edit Room Method
    public function EditRoom($id)
    {
        $basic_facility = Facility::where('rooms_id', $id)->get();
        $multi_images = MultiImage::where('rooms_id', $id)->get();
        $editData = Room::find($id);
        $roomNumbers = RoomNumber::where('rooms_id', $id)->get();
        return view('backend.all_room.rooms.edit_room', compact('editData', 'basic_facility', 'multi_images','roomNumbers'));
    }

    // Update Room Method
    public function UpdateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // =============================
        // VALIDATE
        // =============================
        if (empty($request->facility_name)) {
            return redirect()->back()->with([
                'message' => 'Sorry! Not Any Basic Facility Select',
                'alert-type' => 'error'
            ]);
        }

        // =============================
        // TRANSACTION (safe cho test)
        // =============================
        DB::transaction(function () use ($request, $room) {

            // =============================
            // DATA
            // =============================
            $data = [
                'roomtype_id' => $request->roomtype_id,
                'total_adult' => $request->total_adult,
                'total_child' => $request->total_child,
                'room_capacity' => $request->room_capacity,
                'price' => $request->price,
                'size' => $request->size,
                'view' => $request->view,
                'bed_style' => $request->bed_style,
                'discount' => $request->discount ?? 0,
                'short_desc' => $request->short_desc,
                'description' => $request->description,
            ];

            // =============================
            // MAIN IMAGE
            // =============================
            if ($request->hasFile('image')) {

                $path = public_path('upload/room_images/');

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                // xóa ảnh cũ
                if (!empty($room->image) && File::exists($path . $room->image)) {
                    File::delete($path . $room->image);
                }

                $image = $request->file('image');
                $name = uniqid() . '.' . $image->getClientOriginalExtension();

                if (app()->environment('testing')) {
                    $image->move($path, $name);
                } else {
                    $manager = new ImageManager(new Driver());
                    $manager->read($image)->cover(550, 850)->save($path . $name);
                }

                $data['image'] = $name;
            }

            $room->update($data);

            // =============================
            // FACILITY
            // =============================
            Facility::where('rooms_id', $room->id)->delete();

            foreach ($request->facility_name as $item) {
                Facility::create([
                    'rooms_id' => $room->id,
                    'facility_name' => $item
                ]);
            }

            // =============================
            // MULTI IMAGE
            // =============================
            if ($request->hasFile('multi_img')) {

                $path = public_path('upload/room_images/multi_img/');

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                // Xóa ảnh cũ (file + DB)
                $oldImgs = MultiImage::where('rooms_id', $room->id)->get();

                foreach ($oldImgs as $img) {
                    if (File::exists(public_path($img->multi_img))) {
                        File::delete(public_path($img->multi_img));
                    }
                }

                MultiImage::where('rooms_id', $room->id)->delete();

                // Lưu ảnh mới
                foreach ($request->file('multi_img') as $img) {

                    $name = uniqid() . '.' . $img->getClientOriginalExtension();

                    if (app()->environment('testing')) {
                        $img->move($path, $name);
                    } else {
                        $manager = new ImageManager(new Driver());
                        $manager->read($img)->cover(550, 850)->save($path . $name);
                    }

                    MultiImage::create([
                        'rooms_id' => $room->id,
                        'multi_img' => 'upload/room_images/multi_img/' . $name
                    ]);
                }
            }
        });

        return redirect()->route('room.type.list')->with([
            'message' => 'Update Room Successfully',
            'alert-type' => 'success'
        ]);
    }

    // Multi Image Delete Method
    public function MultiImageDelete($id)
    {
        $deleteData = MultiImage::where('id', $id)->first();

        if ($deleteData) {
            $imagePath = $deleteData->multi_img;

            // Xóa file ảnh nếu tồn tại
            if (file_exists($imagePath)) {
                unlink($imagePath);
                echo "Image Unlinked Successfully";
            } else {
                echo "Image does not exist";
            }

            // Xóa bản ghi trong database
            $deleteData->delete();
        }

        $notification = array(
            'message' => 'Deleted Multi Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}