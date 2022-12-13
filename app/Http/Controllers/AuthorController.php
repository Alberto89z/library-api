<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Exception;
use Illuminate\Http\Request;
use PharIo\Manifest\AuthorElement;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('books')
            ->orderBy('id', 'asc')
            ->get();
        return $this->getResponse200($authors);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $author = new Author();
            $author->name = $request->name;
            $author->first_surname = $request->first_surname;
            $author->second_surname = $request->second_surname;
            $author->save();
            DB::commit();
            return $this->getResponse201('author', 'created', $author);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }
    public function show($id)
    {
        try {
            $author = Author::find($id);
            if ($author) {
                $author = Author::with('books')
                    ->where('id', $id)
                    ->first();
                return $this->getResponse200($author);
            } else {
                return ['message' => 'Not found'];
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $author = Author::find($id);
            if ($author) {
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                $author->second_surname = $request->second_surname;
                $author->update();
                DB::commit();
                $author = Author::with('books')
                    ->where('id', $id)
                    ->get();
                return $this->getResponse201('author', 'updated', $author);
            } else {
                return ['message' => 'Not found'];
            }
        } catch (Exception $e) {
            DB::rollback();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $author = Author::find($id);
        if ($author) {
            $author->books()->detach();
            $author->delete();
            return $this->getResponse201('author', 'deleted', $author);
        } else {
            return $this->getResponse404();
        }
    }
}
