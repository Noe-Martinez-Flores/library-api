<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Exception;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('books')->get();
        return $this->getResponse200($authors);
    }

    public function store(Request $request)
    {
        try {
            $author = new Author();
            $author->name = $request->name;
            $author->first_surname = $request->first_surname;
            if ($request->second_surname) {
                $author->second_surname = $request->second_surname;
            }
            $author->save();
            return $this->getResponse201('author', 'created', $author);
        } catch (Exception $e) {
            return $this->getResponse500($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $author = Author::with('books')->findOrFail($id);
            return $this->getResponse200($author);
        } catch (Exception $e) {
            return $this->getResponse500($e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try {
            $author = Author::find($id);
            if ($author) {
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                if ($request->second_surname) {
                    $author->second_surname = $request->second_surname;
                }
                $author->save();
                return $this->getResponse200($author);
            } else {
                return $this->getResponse404('author');
            }
        } catch (Exception $e) {
            return $this->getResponse500($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $author = Author::with('books')->find($id);
            if ($author) {
                // Eliminar el autor de la tabla intermedia
                // foreach ($author as $item) {
                //     $author->books()->detach($item->id);
                //     $data = [ $author ];
                // }
                // $author->delete();

                // Eliminar el autor de la tabla intermedia y el autor
                $author->books()->detach();
                $author->delete();
                return $this->getResponse200($author);
            } else {
                return $this->getResponse404('author');
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
    }
}
