<?php

namespace App\Http\Controllers;

use App\Models\book;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * @return array
     */
    public function index(): array
    {
        $per_page = 15;
        if (request('per_page')) $per_page = request('per_page');

        $books_collection = book::query()->cursorPaginate($per_page);

        preg_match_all('/\?cursor=(.\w+)/i', $books_collection->previousPageUrl(), $match_prev);

        preg_match_all('/\?cursor=(.\w+)/i', $books_collection->nextPageUrl(), $match_next);

        return [
            'per_page' => $books_collection->perPage(),
            'current_items_in_current_page' => $books_collection->count(),
            'data' => $books_collection->items(),
            'prev_page_cursor' => (isset($match_prev[1][0])) ? $match_prev[1][0] : Null,
            'next_page_cursor' => (isset($match_next[1][0])) ? $match_next[1][0] : Null
        ];
    }
    /**
     * @param Request $request
     * @return array
     */
    public function specific(Request $request)
    {
        $per_page = 15;
        if (request('per_page')) $per_page = request('per_page');
        $query = book::query();
        $attributes = $request->except(['per_page','cursor']);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        $result = $query->cursorPaginate($per_page);

        preg_match_all('/\?cursor=(.\w+)/i', $result->previousPageUrl(), $match_prev);

        preg_match_all('/\?cursor=(.\w+)/i', $result->nextPageUrl(), $match_next);
        return [
            'per_page' => $result->perPage(),
            'current_items_in_current_page' => $result->count(),
            'data' => $result->items(),
            'prev_page_cursor' => (isset($matche_prev[1][0]))? $matche_prev[1][0] : Null,
            'next_page_cursor' => (isset($matche_next[1][0]))? $matche_next[1][0] : Null
        ];
    }

    public function show(Request $request,$id)
    {
        return response(book::find($id));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'author'=>['required','string'],
            'quantity'=>['required','integer'],
            'price'=>['required','numeric'],
            'release_date'=>['required']
        ]);
        if ($validator->fails())
            return response($validator->errors(),422);
        $params = array_merge($request->except('release_date'),
            ['release_date'=>Carbon::parse(request('release_date'))->format('Y-m-d')]);
        $book=book::create($params);
        return response($book,201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'name'=>['required','string'],
            'author'=>['required','string'],
            'quantity'=>['required','integer'],
            'price'=>['required','numeric'],
            'release_date'=>['required','date']
        ]);
        if ($validator->fails())
            return response($validator->errors(),422);

        book::where('id',$id)->update($request->all());

        return response(['message' => 'resource updated / Replaced successfully'],200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'name'=>['string'],
            'author'=>['string'],
            'quantity'=>['integer'],
            'price'=>['numeric'],
            'release_date'=>['date']
        ]);
        if ($validator->fails())
            return response($validator->errors(),422);

        $book = book::find($id);
        foreach ($request->all() as $key => $value){
            if (array_key_exists($key,$book->getAttributes()))
                $book->{$key}=$value;
        }
        if ($book->isDirty())
            $book->save();
        return response(['message' => 'resource attributes updated successfully'],200);

    }

    /**
     * @param book $book
     * @param $id
     * @return Response
     */
    public function destroy(book $book,$id)
    {
        $book_rec = $book->find($id);
        if($book_rec->isNotEmpty()){
            $book_rec->delete();
            return response(['message' => 'resource deleted successfully'], 200);
        }
        else
            return response(['message' => 'resource Not Found '], 404);
    }
}
