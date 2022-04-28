<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Services\BookService;
use Illuminate\Http\Request;

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
        $unit = $request->unit;

        return $this->bookService->bookLinks($isbn, $unit);
    }

}