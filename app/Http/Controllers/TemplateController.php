<?php

namespace App\Http\Controllers;

use App\Models\BillingTemplate;
use App\Models\Invoice;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display template chooser & manager page.
     */
    public function index()
    {
        $templates = BillingTemplate::where('status', 1)->orderBy('id', 'asc')->get();
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Set the store's default active invoice template.
     */
    public function setDefault(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer|exists:billing_templates,id',
        ]);

        StoreSetting::updateOrCreate(
            ['key' => 'default_invoice_template'],
            ['value' => $request->template_id]
        );

        $template = BillingTemplate::find($request->template_id);
        $name = $template ? $template->template_name : 'Selected Template';

        return redirect()->back()->with('success', "'{$name}' is now your default active invoice template!");
    }

    /**
     * Show template data (JSON).
     */
    public function show($id)
    {
        $template = BillingTemplate::findOrFail($id);
        return response()->json(['success' => true, 'data' => $template]);
    }

    /**
     * Save / update template settings.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'id'            => 'nullable|integer|exists:billing_templates,id',
            'template_name' => 'required|string|max:255',
            'template_json' => 'required|string',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'        => 'nullable|integer|in:0,1',
        ]);

        $previewPath = null;
        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');
            $previewPath = $file->store('template', 'public');
        }

        $data = [
            'user_id'       => auth()->id() ?: 1,
            'template_name' => $validated['template_name'],
            'template_json' => $validated['template_json'],
            'status'        => $validated['status'] ?? 1,
        ];

        if ($previewPath) {
            $data['preview_image'] = $previewPath;
        }

        if (!empty($validated['id'])) {
            $template = BillingTemplate::findOrFail($validated['id']);
            $template->update($data);
            $msg = 'Template updated.';
        } else {
            $template = BillingTemplate::create($data);
            $msg = 'Template created.';
        }

        return response()->json(['success' => true, 'message' => $msg, 'template' => $template]);
    }

    /**
     * Delete template.
     */
    public function destroy(Request $request)
    {
        $template = BillingTemplate::find($request->id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }
        $template->delete();
        return response()->json(['success' => true, 'message' => 'Template deleted.']);
    }

    /**
     * Upload an image asset for template.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('template'), $filename);
        $path = 'template/' . $filename;
        return response()->json([
            'success'   => true,
            'image_id'  => $path,
            'image_url' => asset($path),
        ]);
    }
}
