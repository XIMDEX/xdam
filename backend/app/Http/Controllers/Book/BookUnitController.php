<?php

declare(strict_types=1);

namespace App\Http\Controllers\Book;

use App\Http\Requests\RequestResource\Book\Unit\RetriveUnitLinkRequest;
use App\Http\Requests\RequestResource\Book\Unit\DeleteUnitLinkRequest;
use App\Http\Requests\RequestResource\Book\Unit\DeleteUnitsLinkRequest;
use App\Http\Requests\RequestResource\Book\Unit\UpdateLinksRequest;
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

    public function retriveUnitLink(RetriveUnitLinkRequest $request)
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

        return response()->json($links);
    }

    public function updateLinks(UpdateLinksRequest $request)
    {
        if (!$request->has('links')) {
            return response()->noContent(Response::HTTP_BAD_REQUEST);
        }

        $links = $request->input('links');
        $isbn = $request->isbn;


        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->updateBookLinks($book, $links);
    }

    public function deleteUnitLink(DeleteUnitLinkRequest $request)
    {
        $isbn = $request->isbn;
        $unit = (int) $request->unit;

        $book = $this->bookService->findBookFromIsbn($isbn);

        $this->bookService->deleteBookUnitLink($book, $unit);
    }

    public function deleteUnitsLink(DeleteUnitsLinkRequest $request)
    {
        $isbn = $request->isbn;

        $book = $this->bookService->findBookFromIsbn($isbn);
        
        $units = $request->input('units')?? range(0, $book->data->description->unit);

        $this->bookService->deleteBookUnitsLink($book, $units);
    }
}
