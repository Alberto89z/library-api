<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReviews;
use Exception;
use App\Models\BookDownloads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        // $response = $this->getResponseSuccess();
        // //$book = Book::all();
        // $book = Book::with('category','editorial')->get();
        // $response['data']  = $book;
        // return $response;
        $books = Book::with('category', 'editorial', 'authors')
            ->orderBy('title', 'asc')
            ->get();
        return $this->getResponse200($books);
    }

    public function store(Request $request)
    {
        try {
            $isbn = preg_replace('/\s+/', '\u0020', $request->isbn); //Remove blank spaces from ISBN
            $existIsbn = Book::where('isbn', $isbn)->exists(); //Check if a registered book exists (duplicate ISBN)
            DB::beginTransaction();
            if (!$existIsbn) {
                //ISBN not registered
                $book = new Book();
                $book->isbn = $isbn;
                $book->title = $request->title;
                $book->description = $request->description;
                $book->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                $book->category_id = $request->category['id'];
                $book->editorial_id = $request->editorial['id'];
                $book->save();
                foreach ($request->authors as $item) {
                    //Associate authors to book (N:M relationship)
                    $book->authors()->attach($item);
                }
                // $this->storeBookDownload($book);
                $bookDownload = new BookDownloads();
                $bookDownload->book_id = $book->id;
                $bookDownload->save();
                DB::commit();
                $book = Book::with('category', 'editorial', 'authors')
                    ->where('id', $book->id)
                    ->get();
                return $this->getResponse201('book', 'created', $book);
            } else {
                return $this->getResponse500(['The isbn field must be unique']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    private function storeBookDownload(Book $book)
    {
        $bookDownload = new BookDownloads();
        $bookDownload->total_downloads = 0;
        $bookDownload->book_id = $book->id;

        $bookDownload->save();
        $bookDownload->books()->attach($book->id);
    }

    public function show($id)
    {
        $book = Book::with('category', 'editorial', 'authors')
            ->where('id', $id)
            ->first();
        if ($book) {
            return [
                'status' => true,
                'message' => 'Successfull query',
                'data' => $book,
            ];
        } else {
            return ['message' => 'Not found'];
        }
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);
        DB::beginTransaction();
        try {
            if ($book) {
                $isbn = trim($request->isbn);
                $isbnOwner = Book::where('isbn', $isbn)->first();
                if (!$isbnOwner || $isbnOwner->id == $book->id) {
                    //ISBN not registered
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                    $book->category_id = $request->category['id'];
                    $book->editorial_id = $request->editorial['id'];
                    $book->update();
                    // foreach ($request->authors as $item) {
                    //     $book->authors()->detach($item);
                    // }
                    $book->authors()->detach();
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')
                        ->where('id', $id)
                        ->first();
                    DB::commit();
                    return $this->getResponse201('book', 'updated', $book);
                } else {
                    $response['message'] = 'ISBN duplicated';
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage(), $book]);
        }
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        try {
            if ($book) {
                foreach ($book->authors as $item) {
                    $book->authors()->detach($item->id);
                }

                // $book->authors->delete();

                $book->bookDownload->delete();
                // $bookDownload->Books()->detach();
                // $bookDownloads->delete();
                $book->delete();
                return $this->getResponse201('book', 'deleted', $book);
            } else {
                return $this->getResponse404();
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function addBookReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
        ]);
        if (!$validator->fails()) {
            try {
                DB::beginTransaction();
                $bookReview = new BookReviews();
                $book = Book::where('id', $id)->first();
                if ($book) {
                    $bookReview->comment = $request->comment;
                    $bookReview->book_id = $id;
                    $bookReview->user_id = $request->user()->id;
                    $bookReview->save();
                    // $bookReview->books()->attach($id);
                    // $bookReview->users()->attach($$request->user()->id);
                    DB::commit();
                    $bookReview = BookReviews::with('books', 'users')
                        ->where('user_id', $request->user()->id)
                        ->first();
                    return $this->getResponse201(
                        'book review',
                        'created',
                        $bookReview
                    );
                } else {
                    return $this->getResponse404();
                }
            } catch (Exception $e) {
                DB::rollback();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function updateBookReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
        ]);
        if (!$validator->fails()) {
            try {
                DB::beginTransaction();
                $bookReview = BookReviews::find($id);
                if ($bookReview) {
                    if ($bookReview->user_id == $request->user()->id) {
                        $bookReview->comment = $request->comment;
                        $bookReview->edited = true;
                        $bookReview->update();
                        DB::commit();
                        $bookReview = BookReviews::with('books', 'users')
                            ->where('id', $id)
                            ->first();
                        return $this->getResponse201(
                            'book review',
                            'updated',
                            $bookReview
                        );
                    } else {
                        return $this->getResponse403();
                    }
                } else {
                    return $this->getResponse404();
                }
            } catch (Exception $e) {
                DB::rollback();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function showReview($id)
    {
        $bookReview = BookReviews::with('books', 'users')
            ->where('id', $id)
            ->first();
        if ($bookReview) {
            return [
                'status' => true,
                'message' => 'Successfull query',
                'data' => $bookReview,
            ];
        } else {
            return $this->getResponse404();
        }
    }
    public function showReviews()
    {
        $booksReview = BookReviews::with('books', 'users')
            ->orderBy('id', 'asc')
            ->get();
        return $this->getResponse200($booksReview);
    }
}
