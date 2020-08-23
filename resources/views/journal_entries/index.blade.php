@extends ('layouts.app2')
@section ('content')
    <div class="col-md-10">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Journal Entries)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/journal_entries">
                            @csrf
                            <div class="form-group">
                                <label for="explanation">Explanation: </label>
                                <input 
                                    class="form-control @error('explanation') is-danger @enderror" 
                                    type="text" 
                                    name="explanation" 
                                    id="explanation" required
                                    value="{{ old('explanation') }}">
                                @error('explanation')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to record a new journal entry? Click <a href="{{ url('/journal_entries/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($journalEntries as $journalEntry)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $journalEntry->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/journal_entries/{{ $journalEntry->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/journal_entries/{{ $journalEntry->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;({{ $journalEntry->date }},&nbsp;{{ App\Document::where('id', $journalEntry->document_type_id)->firstOrFail()->name }}&nbsp;{{ $journalEntry->document_number }})&nbsp;{{ $journalEntry->explanation }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No journal entries recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
