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

    public function listBookLinks(Request $request)
    {
        $isbn = $request->isbn;
        $unit = (int) $request->unit;

        return $this->bookService->bookLink($isbn, $unit);
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
