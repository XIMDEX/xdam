<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookController extends Controller
{
    private $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function retriveBookUnitLink(Request $request)
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

    public function retriveAllBookUnitsLink(Request $request)
    {
        $isbn = $request->isbn;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $links = $this->bookService->allBookUnitsLink($book);

        if (!$links) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return response()->json((array) $links);
    }

    public function updateBookLinks(Request $request)
    {
        if (!$request->has('links')) {
            return response()->noContent(Response::HTTP_BAD_REQUEST);
        }

        $links = $request->input('links');
        $isbn = $request->isbn;


        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->updateBookLinks($book, $links);
    }

    public function deleteUnitsLinks(Request $request)
    {
        $isbn = $request->isbn;
        $units = $request->input('units');

        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->deleteBookUnitsLink($book, $units);
    }


    public function deleteAllUnitsLinks(Request $request)
    {
        $isbn = $request->isbn;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->deleteBookAllUnitsLink($book);
    }
}
