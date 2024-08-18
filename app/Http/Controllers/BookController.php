<?php

namespace App\Http\Controllers;

use App\Exceptions\IllegalOperationException;
use App\Http\enums\BookStatus;
use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = Book::orderBy('created_at', 'asc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Found',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBookRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        Book::create([
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'synopsis' => $data['synopsis'],
            'path_book' => $data['path_book'],
            'status' => $data['status'],
            'created_by' => $user->id,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Success Created Book',
            'book' => $data
        ]);


    }

    /**
     * Display the specified resource.
     */
    public function searchById(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $book = Book::findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Data found',
                'data' => $book
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }
    }

    public function searchByTitle(string $title, int $perPage = 15, int $page = 1): \Illuminate\Http\JsonResponse
    {
        $books = Book::where('title', 'like', '%' . $title . '%')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($books->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No books found with the given title',
                'data' => [],
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                    'last_page' => $books->lastPage(),
                ]
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Books found',
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'last_page' => $books->lastPage(),
            ]
        ], 200);
    }

    public function findByOwn(int $perPage = 15, int $page = 1)
    {
        $user = Auth::user();

        $books = Book::where('created_by', $user->id)
            ->paginate($perPage, ['*'], 'page', $page);

        if ($books->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'You have no books found',
                'data' => [],
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                    'last_page' => $books->lastPage(),
                ]
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Books found',
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'last_page' => $books->lastPage(),
            ]
        ], 200);
    }

    /**
     * @throws IllegalOperationException
     */
    public function borrowBook(int $bookId): \Illuminate\Http\JsonResponse
    {
        Auth::user();
        $book = Book::findOrFail($bookId);
        if ($book->created_by == Auth::id()) {
            throw new IllegalOperationException("You can't borrow own your book");
        } else if ($book->status != BookStatus::Available->value) {
            throw new IllegalOperationException("");
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function updateBook(UpdateBookRequest $request, int $bookId)
    {
        $data = $request->validated();
        $book = Book::findOrFail($bookId);

        $book->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Book updated successfully',
        ]);
    }

    public function updateStatusBook(int $bookId, UpdateBookRequest $request): void
    {
        $data = $request->validated();
        $book = Book::findOrFail($bookId);
        $book->update($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
