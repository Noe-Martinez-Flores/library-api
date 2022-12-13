<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookDownload;
use App\Models\BookReview;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category', 'authors', 'editorials', 'bookDownload')->get();
        if ($books) {
            return $this->getResponse200($books);
        } else {
            return $this->getResponse404('books');
        }
    }

    public function store(Request $request)
    {
        try {
            $isbn = trim($request->isbn, '');
            $existIsbn = Book::where("isbn", $isbn)->exists();
            if (!$existIsbn) { //ISBN not registered
                $book = new Book();
                $book->isbn = $isbn;
                $book->title = $request->title;
                $book->description = $request->description;
                $book->published_date = date('y-m-d h:i:s');
                $book->category_id = $request->category["id"];
                $book->editorial_id = $request->editorial["id"];
                $book->save();
                $bookDownload = new BookDownload();
                $bookDownload->book_id = $book->id;
                $bookDownload->save();
                foreach ($request->authors as $item) {
                    $book->authors()->attach($item);
                }
                // find the book just registered
                $book = Book::with('category', 'authors', 'editorials', 'bookDownload')->find($book->id);
                return $this->getResponse201('book', 'created', $book);
            } else {
                return $this->getResponse500(['The isbn field must be unique']);
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function update($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $book = Book::with('category', 'authors', 'editorials')->find($id);

            if ($book) {
                $isbn = trim($request->isbn, '');
                if ($book->isbn != $request->isbn) {
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = date('y-m-d h:i:s');
                    $book->category_id = $request->category["id"];
                    $book->editorial_id = $request->editorial["id"];
                    $book->save();

                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item->id);
                    }

                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'authors', 'editorials', 'bookDownload')->find($book->id);
                    DB::commit();
                    return $this->getResponse200($book);
                } else {
                    return $this->getResponse500('ISBN CANNOT BE THE SAME');
                }
            } else {
                return $this->getResponse404('book');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500($e->getMessage());
        }
    }

    public function show($id)
    {
        $book = Book::with('category', 'authors', 'editorials', 'bookDownload')->find($id);
        if ($book) {
            return $this->getResponse200($book);
        } else {
            return $this->getResponse404('book');
        }
    }

    public function delete($id)
    {
        $book = Book::find($id);

        if ($book) {
            foreach ($book->authors as $item) {
                $book->authors()->detach($item->id);
            }
            $book->bookDownload()->delete();
            $book->delete();
            return $this->getResponse200($book);
        } else {
            return $this->getResponse404('book');
        }
    }

    public function addBookReview(Request $request)
    {
        // autenticación de usuario
        if ($request->user()->currentAccessToken()) {
            $book = Book::find($request->id);
            if ($book) {
                $book->bookReviews()->create([
                    'user_id' => $request->user()->id,
                    'book_id' => $book->id,
                    'comment' => $request->comment,
                ]);
                return $this->getResponse200($book);
            } else {
                return $this->getResponse404('book');
            }
        } else {
            return $this->getResponse401();
        }
    }

    public function updateBookReview(Request $request)
    {
        if ($request->user()->currentAccessToken()) {
            $bookReview = BookReview::find($request->id);
            if ( $bookReview ) {
                if ($bookReview ->user_id == $request->user()->id) {
                    $bookReview->comment = $request->comment;
                    $bookReview->edited = true;
                    $bookReview->save();
                    return $this->getResponse200($bookReview);
                } else {
                    return $this->getResponse403();
                }
            }else{
                return $this->getResponse404();
            }
        } else {
            return $this->getResponse401();
        }
    }
}
