@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Edit Inventory Quantity Adjustment)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="/inventory_qty_adjs/{{ $inventoryQtyAdj->id }}">
                                @csrf
                                @method('PUT')
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <datalist id="account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value={{ $account->id }}>{{ $account->title }} ({{ $account->number }})</option>
                                    @endforeach
                                </datalist>
                                <datalist id="product_ids">
                                    @foreach ($products as $product)
                                        <option data-value={{ $product->id }}>{{ $product->name }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-group custom-control-inline">
                                    <label for="date">Date&nbsp;</label>&nbsp;
                                    <input type="date" class="form-control @error('date') is-danger @enderror" id="date" name="date" required value="{!! old('date', $inventoryQtyAdj->date) !!}" oninput="updateLines()">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="number">Reference&nbsp;no.&nbsp;</label>&nbsp;
                                    <input type="number" class="form-control" id="number" name="number" style="text-align: right;" required value="{!! old('number', $inventoryQtyAdj->number) !!}">
                                </div>
                                <br><br>
                                <div class="form-group custom-control-inline">
                                    <label for="account_id">Account</label>&nbsp;
                                    <input list="account_ids" id="account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('account_id') is-danger @enderror" required value="{!! old('account_name', $inventoryQtyAdj->account->title) !!}">
                                    <datalist id="account_ids">
                                        @foreach ($accounts as $account)
                                            <option data-value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="account_id" id="account_id0-hidden" value="{!! old('account_id', $inventoryQtyAdj->account->id) !!}">
                                    <input type="hidden" name="account_name" id="name-account_id0-hidden" value="{!! old('account_name', $inventoryQtyAdj->account->title) !!}">
                                </div>
                                <br><br>
                                <div class="form-group">
                                    <table id="item_lines" style="width:100%">
                                        <tr style="text-align: center;">
                                            <th>
                                                <input type="checkbox" id="myCheck2" onclick="myFunction2()">
                                            </th>
                                            <th>
                                                <label for="item_lines['product_id'][]">Product&#47;Service</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['description'][]">Description</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['qty_on_hand'][]">Qty on Hand</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['new_qty'][]">New Qty</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['change_in_qty'][]">Change in Qty</label>
                                            </th>
                                        <tr>
                                    </table>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" id="addItemLines" onclick="addItemLines('', '', '', '', '', '', '', '')">Add Lines</a>&nbsp;&nbsp;
                                <a class="btn btn-outline-secondary btn-sm" id="deleteItemLines" onclick="deleteItemLines()">Delete Lines</a>
                                <br><br><br>
                                <button class="btn btn-primary" type="submit" style="float: right; clear: both;">Save</button>
                                <input type="hidden" name="product_id" id="product_id" value="">
                            </form>
                            <script>
                                var line = 0;
                                var line2 = 0;
                                var document = new Array();
                                var clines = new Array();
                                var ilines = new Array();
                                var suppliername = '';
                                var accounttitles = new Array();
                                var productnames = new Array();
                                var amounts = new Array();
                                function updateLines()
                                {
                                  product_inputs = $(".item-lines");
                                  for (var i=0, max=product_inputs.length; i < max; i++)
                                  {
                                    updateLine(product_inputs[i]);
                                  }
                                }
                                function updateLine(item, index)
                                {
                                  getQuantities(item);
                                }
                                function getQuantities(adjline)
                                {
                                  var date = document.getElementById('date').value;
                                  var product_id = adjline.parentNode.childNodes[1].value;
                                  document.getElementById('product_id').value = product_id;
                                  let _token = $('meta[name="csrf-token"]').attr('content');
                                  $.ajaxSetup({
                                    headers: {
                                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                  });
                                  $.ajax({
                                    type:'POST',
                                    url:'/inventory_qty_adjs/getquantities',
                                    data: {_token: _token, date: date, product_id: product_id},
                                    dataType: 'json',
                                    success:function(data) {
                                      quantities = data.quantities;
                                      if (getQuantities === null) {
                                          adjline.parentNode.parentNode.childNodes[3].childNodes[0].value = '';
                                          adjline.parentNode.parentNode.childNodes[4].childNodes[0].value = '';
                                      }
                                      else {
                                          displayQuantities(adjline, quantities);
                                      }
                                    },
                                    error: function(data){
                                    }
                                  });
                                }
                                function displayQuantities(adjline, quantities)
                                {
                                    adjline.parentNode.parentNode.childNodes[3].childNodes[0].value = quantities["qty_on_hand"];
                                    adjline.parentNode.parentNode.childNodes[4].childNodes[0].value = quantities["qty_on_hand"];
                                    adjline.parentNode.parentNode.childNodes[5].childNodes[0].value = '';
                                }
                                function updateChangeInQty(adjline)
                                {
                                  qty_on_hand = adjline.parentNode.parentNode.childNodes[3].childNodes[0].value;
                                  new_qty = adjline.parentNode.parentNode.childNodes[4].childNodes[0].value;
                                  change_in_qty = new_qty - qty_on_hand;
                                  adjline.parentNode.parentNode.childNodes[5].childNodes[0].value = change_in_qty;
                                }
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
                                function addCategoryLines(a, b, c, d, e) {
                                    var tr = document.createElement("tr");
                                    tr.setAttribute("class", "clines");
                                    var table = document.getElementById("category_lines");
                                    table.appendChild(tr);
                                    var td0 = document.createElement("td");
                                    tr.appendChild(td0);
                                    var check = document.createElement("input");
                                    check.setAttribute("type", "checkbox");
                                    check.setAttribute("class", "deleteBox");
                                    td0.appendChild(check);
                                    var td1 = document.createElement("td");
                                    tr.appendChild(td1);
                                    var accountInput = document.createElement("input");
                                    accountInput.setAttribute("list", "account_ids");
                                    accountInput.setAttribute("id", "category_lines['account_id'][]" + line);
                                    accountInput.setAttribute("onchange", "setValue(this)");
                                    accountInput.setAttribute("data-id", line);
                                    accountInput.setAttribute("class", "custom-select");
                                    accountInput.setAttribute("required", "required");
                                    accountInput.setAttribute("value", e);
                                    td1.appendChild(accountInput);
                                    var accountHidden = document.createElement("input");
                                    accountHidden.setAttribute("type", "hidden");
                                    accountHidden.setAttribute("name", "category_lines['account_id'][]");
                                    accountHidden.setAttribute("id", "category_lines['account_id'][]" + line + "-hidden");
                                    accountHidden.setAttribute("value", a);
                                    td1.appendChild(accountHidden);
                                    var accountHidden2 = document.createElement("input");
                                    accountHidden2.setAttribute("type", "hidden");
                                    accountHidden2.setAttribute("name", "category_lines['account_name'][]");
                                    accountHidden2.setAttribute("id", "name-category_lines['account_id'][]" + line + "-hidden");
                                    accountHidden2.setAttribute("value", e);
                                    td1.appendChild(accountHidden2);
                                    var td2 = document.createElement("td");
                                    tr.appendChild(td2);
                                    var descriptionInput = document.createElement("input");
                                    descriptionInput.setAttribute("type", "text");
                                    descriptionInput.setAttribute("class", "form-control");
                                    descriptionInput.setAttribute("id", "category_lines['description'][]" + line);
                                    descriptionInput.setAttribute("name", "category_lines['description'][]");
                                    descriptionInput.setAttribute("style", "text-align: left;");
                                    descriptionInput.setAttribute("value", b);
                                    td2.appendChild(descriptionInput);
                                    var td3 = document.createElement("td");
                                    tr.appendChild(td3);
                                    var amountInput = document.createElement("input");
                                    amountInput.setAttribute("type", "number");
                                    amountInput.setAttribute("class", "form-control amount");
                                    amountInput.setAttribute("id", "category_lines['amount'][]" + line);
                                    amountInput.setAttribute("name", "category_lines['amount'][]");
                                    amountInput.setAttribute("step", "0.01");
                                    amountInput.setAttribute("style", "text-align: right;");
                                    amountInput.setAttribute("value", c);
                                    amountInput.setAttribute("oninput", "updateSubtotal()");
                                    td3.appendChild(amountInput);
                                    var td4 = document.createElement("td");
                                    tr.appendChild(td4);
                                    var inputTaxInput = document.createElement("input");
                                    inputTaxInput.setAttribute("type", "number");
                                    inputTaxInput.setAttribute("class", "form-control tax");
                                    inputTaxInput.setAttribute("id", "category_lines['input_tax'][]" + line);
                                    inputTaxInput.setAttribute("name", "category_lines['input_tax'][]");
                                    inputTaxInput.setAttribute("step", "0.01");
                                    inputTaxInput.setAttribute("style", "text-align: right;");
                                    inputTaxInput.setAttribute("value", d);
                                    inputTaxInput.setAttribute("oninput", "updateTotalTax()");
                                    td4.appendChild(inputTaxInput);
                                    line++;
                                }
                                function addItemLines(a, b, c, d, e, f) {
                                    var tr = document.createElement("tr");
                                    tr.setAttribute("class", "ilines");
                                    var table = document.getElementById("item_lines");
                                    table.appendChild(tr);
                                    var td0 = document.createElement("td");
                                    tr.appendChild(td0);
                                    var check = document.createElement("input");
                                    check.setAttribute("type", "checkbox");
                                    check.setAttribute("class", "deleteBox2");
                                    td0.appendChild(check);
                                    var td1 = document.createElement("td");
                                    tr.appendChild(td1);
                                    var productInput = document.createElement("input");
                                    productInput.setAttribute("list", "product_ids");
                                    productInput.setAttribute("id", "item_lines['product_id'][]" + line2);
                                    productInput.setAttribute("oninput", "setValue(this); getQuantities(this);");
                                    productInput.setAttribute("data-id", line2);
                                    productInput.setAttribute("class", "custom-select item-lines");
                                    productInput.setAttribute("required", "required");
                                    productInput.setAttribute("value", f);
                                    td1.appendChild(productInput);
                                    var productHidden = document.createElement("input");
                                    productHidden.setAttribute("type", "hidden");
                                    productHidden.setAttribute("name", "item_lines['product_id'][]");
                                    productHidden.setAttribute("id", "item_lines['product_id'][]" + line2 + "-hidden");
                                    productHidden.setAttribute("value", a);
                                    td1.appendChild(productHidden);
                                    var productHidden2 = document.createElement("input");
                                    productHidden2.setAttribute("type", "hidden");
                                    productHidden2.setAttribute("name", "item_lines['product_name'][]");
                                    productHidden2.setAttribute("id", "name-item_lines['product_id'][]" + line2 + "-hidden");
                                    productHidden2.setAttribute("value", f);
                                    td1.appendChild(productHidden2);
                                    var td2 = document.createElement("td");
                                    tr.appendChild(td2);
                                    var descriptionInput = document.createElement("input");
                                    descriptionInput.setAttribute("type", "text");
                                    descriptionInput.setAttribute("class", "form-control");
                                    descriptionInput.setAttribute("id", "item_lines['description'][]" + line2);
                                    descriptionInput.setAttribute("name", "item_lines['description'][]");
                                    descriptionInput.setAttribute("style", "text-align: left;");
                                    descriptionInput.setAttribute("value", b);
                                    td2.appendChild(descriptionInput);
                                    var td3 = document.createElement("td");
                                    tr.appendChild(td3);
                                    var quantityInput = document.createElement("input");
                                    quantityInput.setAttribute("type", "number");
                                    quantityInput.setAttribute("class", "form-control");
                                    quantityInput.setAttribute("id", "item_lines['quantity'][]" + line2);
                                    quantityInput.setAttribute("name", "item_lines['quantity'][]");
                                    quantityInput.setAttribute("step", "0.001");
                                    quantityInput.setAttribute("style", "text-align: right;");
                                    quantityInput.setAttribute("value", c);
                                    td3.appendChild(quantityInput);
                                    var td4 = document.createElement("td");
                                    tr.appendChild(td4);
                                    var amountInput = document.createElement("input");
                                    amountInput.setAttribute("type", "number");
                                    amountInput.setAttribute("class", "form-control amount");
                                    amountInput.setAttribute("id", "item_lines['amount'][]" + line2);
                                    amountInput.setAttribute("name", "item_lines['amount'][]");
                                    amountInput.setAttribute("step", "0.01");
                                    amountInput.setAttribute("style", "text-align: right;");
                                    amountInput.setAttribute("value", d);
                                    amountInput.setAttribute("oninput", "updateChangeInQty(this)");
                                    td4.appendChild(amountInput);
                                    var td5 = document.createElement("td");
                                    tr.appendChild(td5);
                                    var inputTaxInput = document.createElement("input");
                                    inputTaxInput.setAttribute("type", "number");
                                    inputTaxInput.setAttribute("class", "form-control tax");
                                    inputTaxInput.setAttribute("id", "item_lines['change_in_qty'][]" + line2);
                                    inputTaxInput.setAttribute("name", "item_lines['change_in_qty'][]");
                                    inputTaxInput.setAttribute("step", "0.01");
                                    inputTaxInput.setAttribute("style", "text-align: right;");
                                    inputTaxInput.setAttribute("value", e);
                                    inputTaxInput.setAttribute("oninput", "updateTotalTax()");
                                    td5.appendChild(inputTaxInput);
                                    line2++;
                                }
                                function myFunction() {
                                    var checkBox = document.getElementById("myCheck");
                                    if (checkBox.checked == true)
                                    {
                                        var x = document.getElementsByClassName("deleteBox");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = true;
                                        }
                                    }
                                    else
                                    {
                                        var x = document.getElementsByClassName("deleteBox");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = false;
                                        }
                                    }
                                }
                                function myFunction2() {
                                    var checkBox = document.getElementById("myCheck2");
                                    if (checkBox.checked == true)
                                    {
                                        var x = document.getElementsByClassName("deleteBox2");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = true;
                                        }
                                    }
                                    else
                                    {
                                        var x = document.getElementsByClassName("deleteBox2");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = false;
                                        }
                                    }
                                }
                                function deleteCategoryLines () {
                                    var x = document.getElementsByClassName("deleteBox");
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if (x[i].checked) {
                                            x[i].parentNode.parentNode.remove();
                                        }
                                    }
                                }
                                function deleteItemLines () {
                                    var x = document.getElementsByClassName("deleteBox2");
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if (x[i].checked) {
                                            x[i].parentNode.parentNode.remove();
                                        }
                                    }
                                }
                                function updateSubtotal() {
                                    var x = document.getElementsByClassName("amount");
                                    var sum = 0;
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if(isNaN(parseInt(x[i].value))){
                                            continue;
                                        }
                                        sum += parseInt(x[i].value);
                                    }
                                    document.getElementById("subtotal").value = sum;
                                    updateTotal();
                                }
                                function updateTotalTax() {
                                    var x = document.getElementsByClassName("tax");
                                    var sum = 0;
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if(isNaN(parseInt(x[i].value))){
                                            continue;
                                        }
                                        sum += parseInt(x[i].value);
                                    }
                                    document.getElementById("total_tax").value = sum;
                                    updateTotal();
                                }
                                function updateTotal() {
                                    subtotal = parseInt(document.getElementById("subtotal").value);
                                    tax = parseInt(document.getElementById("total_tax").value);
                                    if(isNaN(tax)) {
                                        document.getElementById("total").value = subtotal;
                                    }
                                    else {
                                        if(isNaN(subtotal)) {
                                            document.getElementById("total").value = tax;
                                        }
                                        else {
                                            document.getElementById("total").value = subtotal + tax;
                                        }
                                    }
                                }
                                @if (!empty(old('item_lines')))
                                    var a = <?php echo json_encode(old("item_lines.'product_id'")); ?>;
                                    var b = <?php echo json_encode(old("item_lines.'description'")); ?>;
                                    var c = <?php echo json_encode(old("item_lines.'quantity'")); ?>;
                                    var d = <?php echo json_encode(old("item_lines.'amount'")); ?>;
                                    var e = <?php echo json_encode(old("item_lines.'change_in_qty'")); ?>;
                                    var f = <?php echo json_encode(old("item_lines.'product_name'")); ?>;
                                    var i;
                                    for (i = 0; i < a.length; i++)
                                    {
                                        if(a[i] == null) {a[i] = "";}
                                        if(b[i] == null) {b[i] = "";}
                                        if(c[i] == null) {c[i] = "";}
                                        if(d[i] == null) {d[i] = "";}
                                        if(e[i] == null) {e[i] = "";}
                                        if(f[i] == null) {f[i] = "";}
                                        addItemLines(a[i], b[i], c[i], d[i], e[i], f[i]);
                                    }
                                    updateSubtotal();
                                    updateTotalTax();
                                    updateTotal();
                                @else
                                    @if (!empty($inventoryQtyAdj->lines))
                                        @foreach ($inventoryQtyAdj->lines as $itemLine)
                                            var itemLine = <?php echo json_encode($itemLine); ?>;
                                            var a = itemLine['product_id'];
                                            var b = itemLine['description'];
                                            var c = '';
                                            var d = '';
                                            var e = itemLine['change_in_qty'];
                                            var f = <?php echo json_encode(\App\Product::where('id', $itemLine->product_id)->firstOrFail()->name); ?>;
                                            if(a == null) {a = "";}
                                            if(b == null) {b = "";}
                                            if(c == null) {c = "";}
                                            if(d == null) {d = "";}
                                            if(e == null) {e = "";}
                                            if(f == null) {f = "";}
                                            addItemLines(a, b, c, d, e, f);
                                        @endforeach
                                    @endif
                                @endif
                                function updateTotals() {
                                    updateSubtotal();
                                    updateTotalTax();
                                    updateTotal();
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection