<?php

declare(strict_types=1);

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookUnitController extends Controller
{
    private $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function retriveUnitLink(Request $request)
    {
        $isbn = $request->isbn;
        $unit = (int) $request->unit;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $link = $this->bookService->bookUnitLink($book, $unit);

        if(!$link) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "link" => $link
        ]);
    }

    public function retriveAllUnitsLink(Request $request)
    {
        $isbn = $request->isbn;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $links = $this->bookService->allBookUnitsLink($book);

        if (!$links) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return response()->json((array) $links);
    }

    public function updateLinks(Request $request)
    {
        if (!$request->has('links')) {
            return response()->noContent(Response::HTTP_BAD_REQUEST);
        }

        $links = $request->input('links');
        $isbn = $request->isbn;


        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->updateBookLinks($book, $links);
    }

    public function deleteUnitsLink(Request $request)
    {
        $isbn = $request->isbn;
        $units = $request->input('units');

        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->deleteBookUnitsLink($book, $units);
    }


    public function deleteAllUnitsLink(Request $request)
    {
        $isbn = $request->isbn;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->deleteBookAllUnitsLink($book);
    }
}
