<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::latest()->get();
        return response()->json(['data' => $testimonials]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $testimonial = Testimonial::create([
            'customer_name' => $request->customer_name,
            'content' => $request->content,
            'rating' => $request->rating,
            'is_featured' => $request->is_featured ?? true,
        ]);

        return response()->json(['message' => 'Testimoni berhasil ditambahkan', 'data' => $testimonial], 201);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $testimonial->update([
            'customer_name' => $request->customer_name,
            'content' => $request->content,
            'rating' => $request->rating,
            'is_featured' => $request->has('is_featured') ? $request->is_featured : $testimonial->is_featured,
        ]);

        return response()->json(['message' => 'Testimoni berhasil diperbarui', 'data' => $testimonial]);
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json(['message' => 'Testimoni berhasil dihapus']);
    }
}