<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $products = Product::active()->get();
        return view('landing', compact('products'));
    }

    public function cart()
    {
        return view('cart');
    }

    public function orderForm()
    {
        return view('order-form');
    }

    public function orderStatus($orderCode)
    {
        return view('order-status', compact('orderCode'));
    }
}
