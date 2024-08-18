<?php

namespace App\Http\Controllers\api;

use App\Exceptions\IllegalOperationException;
use App\Http\Controllers\Controller;
use App\Http\enums\BookStatus;
use App\Http\Requests\CreateFeedbackRequest;
use App\Models\Book;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * @throws IllegalOperationException
     */
    public function create(CreateFeedbackRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        $book = Book::find($data['book_id']);

        if ($book['status'] != BookStatus::Available->value) {
            throw new IllegalOperationException("cannot create feedback due to book not available");
        }

        $data['created_by'] = $user->id;
        $feedback = Feedback::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Feedback created successfully',
            'feedback' => $feedback,
        ], 201);
    }

    public function findAllFeedbacksByBook(int $bookId, int $page = 1, int $perPage = 15): \Illuminate\Http\JsonResponse
    {
        $data = Feedback::where('book_id', $bookId)
            ->orderBy('id', 'asc')
            ->get()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

}
