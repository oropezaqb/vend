@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Search</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">

                        There are {{ $searchResults->count() }} results.

                        @foreach($searchResults->groupByType() as $type => $modelSearchResults)
                           <h2>{{ $type }}</h2>
                           
                           @foreach($modelSearchResults as $searchResult)
                               <ul>
                                    <li><a href="{{ $searchResult->url }}">{{ $searchResult->title }}</a></li>
                               </ul>
                           @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
