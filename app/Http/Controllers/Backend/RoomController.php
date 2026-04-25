<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RoomController extends Controller
{
    // Edit Room Method
    public function EditRoom($id)
    {
        $basic_facility = Facility::where('rooms_id', $id)->get();
        $editData = Room::find($id);
        return view('backend.all_room.rooms.edit_room', compact('editData', 'basic_facility'));
    }

    // Update Room
    public function UpdateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // Check if facilities are selected
        $facilities = $request->facility_name;
        
        // Count non-empty facilities
        $selectedFacilities = array_filter($facilities, function($value) {
            return !empty($value);
        });

        if (empty($selectedFacilities)) {
            return redirect()->back()->with([
                'message' => 'Sorry! Not Any Basic Facility Select',
                'alert-type' => 'error'
            ]);
        }

        $data = [
            'roomtype_id' => $request->roomtype_id,
            'total_adult' => $request->total_adult,
            'total_child' => $request->total_child,
            'room_capacity' => $request->room_capacity,
            'price' => $request->price,
            'size' => $request->size,
            'view' => $request->view,
            'bed_style' => $request->bed_style,
            'discount' => $request->discount,
            'short_desc' => $request->short_desc,
            'description' => $request->description,
        ];

        // Handle main image
        if ($request->file('image')) {
            if (!empty($room->image) && File::exists(public_path('upload/room_images/' . $room->image))) {
                File::delete(public_path('upload/room_images/' . $room->image));
            }

            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            $folder = public_path('upload/room_images');
            File::ensureDirectoryExists($folder);

            $manager = new ImageManager(new Driver());
            $manager->read($image)
                ->cover(550, 850)
                ->save($folder . '/' . $name_gen);

            $data['image'] = $name_gen;
        }

        // Update room data
        $room->update($data);

        // Handle gallery images
        if ($request->file('multi_img')) {
            $folder = public_path('upload/room_images');
            File::ensureDirectoryExists($folder);

            foreach ($request->file('multi_img') as $image) {
                $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

                $manager = new ImageManager(new Driver());
                $manager->read($image)
                    ->cover(550, 850)
                    ->save($folder . '/' . $name_gen);

                // Save to multi_images table
                MultiImage::create([
                    'rooms_id' => $room->id,
                    'multi_img' => $name_gen,
                ]);
            }
        }

        // Handle facilities: Delete old and add new
        // First, delete all existing facilities for this room
        Facility::where('rooms_id', $room->id)->delete();

        // Add new facilities
        $facilityCount = count($selectedFacilities);
        foreach ($selectedFacilities as $facilityName) {
            Facility::create([
                'rooms_id' => $room->id,
                'facility_name' => $facilityName,
            ]);
        }

        return redirect()->route('room.type.list')->with([
            'message' => 'Update Room Successfully',
            'alert-type' => 'success'
        ]);
    }
}
