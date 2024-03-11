<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        // get books
        $books = Book::all();

        // return collection of books as a resource
        return new BookResource(true,'List Data Post',$books);
        
    }

    public function store(Request $request)
    {
        // define validator rules
        $validator = Validator::make($request->all(),[
            'title'       => 'required',
            'cover'       => 'image|mimes:png,jpg|max:2048',
            'author'      => 'required',
            'description' => 'required'
        ]);

        // check validator
        if($validator->fails()) {
            return response()->json($validator->errors(),422);
        }else{
            
            if($request->hasFile('cover')){
                // uplaod image
                $image = $request->file('cover');
                $fileName = time().'.'.$image->getClientOriginalExtension();
                Storage::disk('local')->putFileAs('public/books',$image,$fileName);
            }

            
            // create book
            $data = Book::create([
                'title'       => $request->title,
                'cover'       => $fileName,
                'author'      => $request->author,
                'description' => $request->description
            ]);
            
            return new BookResource(true,'Data book berhasil di tambahkan',$data);
        }
    }

    public function show(Book $book)
    {

        return new BookResource(true,"Data Book Ditemukan!",$book);

    }

    public function update(Request $request,Book $book)
    {

         // define validator rules
        $validator = Validator::make($request->all(),[
            'title'       => 'required',
            'cover'       => 'image|mimes:png,jpg|max:2048',
            'author'      => 'required',
            'description' => 'required'
        ]);

        // check validator
        if($validator->fails()) {
            return response()->json($validator->errors(),422);
        }else{
            if($request->hasFile('cover')){
                // uplaod image
                $image = $request->file('cover');
                $fileName = time().'.'.$image->getClientOriginalExtension();
                Storage::disk('local')->putFileAs('public/books',$image,$fileName);

                // delete old image
                Storage::delete('public/books/'.$book->cover);
                
                $book->update([
                    'cover'       => $fileName
                ]);
            }

        }

        // update book
        $book->update([
            'title'       => $request->title,
            'author'      => $request->author,
            'description' => $request->description
        ]);
        
        return new BookResource(true,'Data Book Berhasil Diubah',$book);

    }

    public function destroy(Book $book)
    {
        

        Storage::delete('public/books/'.$book->cover);

        $book->delete();

        return new BookResource(true,'data book '.$book->title.' berhasil dihapus!',$book);
    }
}
