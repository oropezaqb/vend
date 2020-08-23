@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add Queries)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <form method="POST" action="/queries">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="title">Query Title: </label>
                                    <input 
                                        class="form-control" 
                                        type="text" 
                                        name="title" 
                                        id="title" required
                                        value="{{ old('title') }}">
                                </div>
                                <div class="form-group">
                                    <label for="category">Category: </label>
                                    <input 
                                        class="form-control" 
                                        type="text" 
                                        name="category" 
                                        id="category"
                                        value="{{ old('category') }}">
                                </div>
                                <div class="form-group">
                                    <label for="query">Query: </label>
                                    <textarea class="form-control" rows="5" id="query" name="query" required>{{ old('query') }}</textarea>
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="ability_id">Ability:&nbsp;</label>&nbsp;
                                    <input list="ability_ids" id="ability_id0" onchange="setValue(this)" data-id="" class="custom-select" required value="{!! old('ability_name') !!}">
                                    <datalist id="ability_ids">
                                        @foreach ($abilities as $ability)
                                            <option data-value="{{ $ability->id }}">{{ $ability->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="ability_id" id="ability_id0-hidden" value="{!! old('ability_id') !!}">
                                    <input type="hidden" name="ability_name" id="name-ability_id0-hidden" value="{!! old('ability_name') !!}">
                                </div>
                                <br>
                                <button class="btn btn-primary" type="submit">Add</button>
                            </form>
                            <script>
                                function setValue (id) 
                                {
                                    var input = id,
                                        list = input.getAttribute('list'),
                                        options = document.querySelectorAll('#' + list + ' option'),
                                        hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden'),
                                        hiddenInputName = document.getElementById('name-' + input.getAttribute('id') + '-hidden'),
                                        label = input.value;

                                    hiddenInputName.value = label;
                                    hiddenInput.value = label;

                                    for(var i = 0; i < options.length; i++) {
                                        var option = options[i];

                                        if(option.innerText === label) {
                                            hiddenInput.value = option.getAttribute('data-value');
                                            break;
                                        }
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
