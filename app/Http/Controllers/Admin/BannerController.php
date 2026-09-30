<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderByDesc('id')->paginate(20);
        return view('admin.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        $rules = [
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:20480',
            'primary_text' => 'nullable|string|max:255',
            'tagline'      => 'nullable|string|max:255',
            'show_button'  => 'boolean',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'is_flash_sale'=> 'boolean',
        ];

        if ($request->boolean('show_button')) {
            $rules['button_name'] = 'required|string|max:100';
            $rules['button_link'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = $file->store('banner', 'public');
        }

        Banner::create([
            'image'        => $imagePath,
            'primary_text' => $validated['primary_text'] ?? null,
            'tagline'      => $validated['tagline'] ?? null,
            'show_button'  => $request->boolean('show_button'),
            'button_name'  => $request->boolean('show_button') ? ($validated['button_name'] ?? null) : null,
            'button_link'  => $request->boolean('show_button') ? ($validated['button_link'] ?? null) : null,
            'sort_order'   => $validated['sort_order'] ?? 0,
            'is_active'    => $request->boolean('is_active', true),
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'is_flash_sale'=> $request->boolean('is_flash_sale'),
        ]);

        return response()->json(['success' => true, 'message' => 'Banner created successfully.']);
    }

    public function update(Request $request, Banner $banner)
    {
        $rules = [
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:20480',
            'primary_text' => 'nullable|string|max:255',
            'tagline'      => 'nullable|string|max:255',
            'show_button'  => 'boolean',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'is_flash_sale'=> 'boolean',
        ];

        if ($request->boolean('show_button')) {
            $rules['button_name'] = 'required|string|max:100';
            $rules['button_link'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $imagePath = $banner->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($imagePath) {
                if (file_exists(public_path($imagePath))) {
                    @unlink(public_path($imagePath));
                } else {
                    Storage::disk('public')->delete(str_replace('storage/', '', $imagePath));
                }
            }
            // Upload new image
            $file = $request->file('image');
            $imagePath = $file->store('banner', 'public');
        }

        $banner->update([
            'image'        => $imagePath,
            'primary_text' => $validated['primary_text'] ?? null,
            'tagline'      => $validated['tagline'] ?? null,
            'show_button'  => $request->boolean('show_button'),
            'button_name'  => $request->boolean('show_button') ? ($validated['button_name'] ?? null) : null,
            'button_link'  => $request->boolean('show_button') ? ($validated['button_link'] ?? null) : null,
            'sort_order'   => $validated['sort_order'] ?? 0,
            'is_active'    => $request->boolean('is_active', true),
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'is_flash_sale'=> $request->boolean('is_flash_sale'),
        ]);

        return response()->json(['success' => true, 'message' => 'Banner updated successfully.']);
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            if (file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            } else {
                Storage::disk('public')->delete(str_replace('storage/', '', $banner->image));
            }
        }
        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Banner deleted successfully.']);
    }
}
