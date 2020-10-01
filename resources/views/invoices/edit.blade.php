@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Edit Invoice)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="/invoices/{{ $invoice->id }}">
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
                                <datalist id="customer_ids">
                                    @foreach ($customers as $customer)
                                        <option data-value={{ $customer->id }}>{{ $customer->name }}</option>
                                    @endforeach
                                </datalist>
                                <datalist id="product_ids">
                                    @foreach ($products as $product)
                                        <option data-value={{ $product->id }}>{{ $product->name }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-group custom-control-inline">
                                    <label for="customer_id">Customer</label>&nbsp;
                                    <input list="customer_ids" id="customer_id0" onchange="setValue(this)" data-id="" class="custom-select @error('customer_id') is-danger @enderror" required value="{!! old('customer_name', $invoice->customer->name) !!}">
                                    <datalist id="customer_ids">
                                        @foreach ($customers as $customer)
                                            <option data-value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="customer_id" id="customer_id0-hidden" value="{!! old('customer_id', $invoice->customer_id) !!}">
                                    <input type="hidden" name="customer_name" id="name-customer_id0-hidden" value="{!! old('customer_name', $invoice->customer->name) !!}">
                                </div>
                                <br><br>
                                <div class="form-group custom-control-inline">
                                    <label for="invoice_date">Invoice&nbsp;date&nbsp;</label>&nbsp;
                                    <input type="date" class="form-control @error('invoice_date') is-danger @enderror" id="invoice_date" name="invoice_date" required value="{!! old('invoice_date', $invoice->invoice_date) !!}">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="due_date">Due&nbsp;date&nbsp;</label>&nbsp;
                                    <input type="date" class="form-control @error('due_date') is-danger @enderror" id="due_date" name="due_date" required value="{!! old('due_date', $invoice->due_date) !!}">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="invoice_number">Invoice&nbsp;no.&nbsp;</label>&nbsp;
                                    <input type="invoice_number" class="form-control" id="invoice_number" name="invoice_number" style="text-align: right;" required value="{!! old('invoice_number', $invoice->invoice_number) !!}">
                                </div>
                                <br><br>
                                <div style="text-align: right;"><p>Amounts are Exclusive of Tax</p></div>
                                <h6 class="font-weight-bold">Item details</h6>
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
                                                <label for="item_lines['quantity'][]">Qty</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['amount'][]">Amount</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['output_tax'][]">Tax</label>
                                            </th>
                                        <tr>
                                    </table>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" id="addItemLines" onclick="addItemLines('', '', '', '', '', '', '', '')">Add Lines</a>&nbsp;&nbsp;
                                <a class="btn btn-outline-secondary btn-sm" id="deleteItemLines" onclick="deleteItemLines()">Delete Lines</a>
                                <br><br>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="subtotal">Subtotal</label>&nbsp;
                                    <input id="subtotal" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="total_tax">Total&nbsp;tax</label>&nbsp;
                                    <input id="total_tax" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="total">Total</label>&nbsp;
                                    <input id="total" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <br><br><br>
                                <button class="btn btn-primary" type="submit" style="float: right; clear: both;">Save</button>
                            </form>
                            <script>
                                var line = 0;
                                var line2 = 0;
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
                                function addItemLines(a, b, c, d, e, f) {

                                    var tr = document.createElement("tr");
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
                                    productInput.setAttribute("onchange", "setValue(this)");
                                    productInput.setAttribute("data-id", line2);
                                    productInput.setAttribute("class", "custom-select");
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
                                    amountInput.setAttribute("oninput", "updateSubtotal()");
                                    td4.appendChild(amountInput);

                                    var td5 = document.createElement("td");
                                    tr.appendChild(td5);

                                    var inputTaxInput = document.createElement("input");
                                    inputTaxInput.setAttribute("type", "number");
                                    inputTaxInput.setAttribute("class", "form-control tax");
                                    inputTaxInput.setAttribute("id", "item_lines['output_tax'][]" + line2);
                                    inputTaxInput.setAttribute("name", "item_lines['output_tax'][]");
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
                                    var e = <?php echo json_encode(old("item_lines.'output_tax'")); ?>;
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
                                    @if (!empty($invoice->itemLines))
                                        @foreach ($invoice->itemLines as $itemLine)
                                            var itemLine = <?php echo json_encode($itemLine); ?>;
                                            var a = itemLine['product_id'];
                                            var b = itemLine['description'];
                                            var c = itemLine['quantity'];
                                            var d = itemLine['amount'];
                                            var e = itemLine['output_tax'];
                                            var f = <?php echo json_encode(\App\Product::where('id', $itemLine->product_id)->firstOrFail()->name); ?>;
                                            if(a == null) {a = "";}
                                            if(b == null) {b = "";}
                                            if(c == null) {c = "";}
                                            if(d == null) {d = "";}
                                            if(e == null) {e = "";}
                                            if(f == null) {f = "";}
                                            addItemLines(a, b, c, d, e, f);
                                            updateTotals();
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
