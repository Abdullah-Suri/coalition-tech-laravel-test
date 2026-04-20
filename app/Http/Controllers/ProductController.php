<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $jsonFile = 'inventory.json';

    public function index()
    {
        return view('products');
    }

    public function fetchRecords()
    {
        return response()->json($this->readJson());
    }

    public function store(Request $request)
    {
        $inputs = $request->validate([
            'name' => 'required|string|max:200',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        $items = $this->readJson();
        
        $items[] = [
            'id' => uniqid(),
            'name' => $inputs['name'],
            'quantity' => intval($inputs['quantity']),
            'price' => floatval($inputs['price']),
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        $this->writeJson($items);

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function update(Request $request, $id)
    {
        $inputs = $request->validate([
            'name' => 'required|string|max:200',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        $items = $this->readJson();
        
        foreach ($items as $key => $val) {
            if ($val['id'] == $id) {
                $items[$key]['name'] = $inputs['name'];
                $items[$key]['quantity'] = intval($inputs['quantity']);
                $items[$key]['price'] = floatval($inputs['price']);
                break;
            }
        }

        $this->writeJson($items);

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function destroy($id)
    {
        $items = $this->readJson();
        $filtered = [];
        
        foreach($items as $item) {
            if($item['id'] !== $id) {
                $filtered[] = $item;
            }
        }

        $this->writeJson($filtered);

        return response()->json(['status' => 'success', 'data' => $filtered]);
    }

    private function readJson()
    {
        if (!Storage::exists($this->jsonFile)) {
            return [];
        }
        
        $content = Storage::get($this->jsonFile);
        $decoded = json_decode($content, true);
        
        return $decoded ? $decoded : [];
    }

    private function writeJson($dataArray)
    {
        Storage::put($this->jsonFile, json_encode($dataArray, JSON_PRETTY_PRINT));
    }
}