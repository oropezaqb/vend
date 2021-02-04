<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>InnoBooks</title>

  <!-- Custom fonts for this template-->
  <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
  <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
  <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon">
          <!-- <i class="fas fa-laugh-wink"></i> -->
          <img
            style="height: 2rem; width: 2rem; background-color: white;"
          src="{{ asset('img/inno.png') }}" alt="">
        </div>
        <div class="sidebar-brand-text mx-3">InnoBooks</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - New -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
          <i class="fas fa-fw fa-plus"></i>
          <span>New</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Customers</h6>
            <a class="collapse-item" href="/invoices">Invoice</a>
            <a class="collapse-item" href="/received_payments">Receive payment</a>
            <a class="collapse-item" href="/creditnote">Credit note</a>
            <a class="collapse-item" href="/sales_receipts">Sales receipt</a>
            <a class="collapse-item" href="/refundreceipt">Refund receipt</a>
            <h6 class="collapse-header">Suppliers</h6>
            <a class="collapse-item" href="#">Expense</a>
            <a class="collapse-item" href="#">Cheque</a>
            <a class="collapse-item" href="/bills">Bill</a>
            <a class="collapse-item" href="#">Pay bills</a>
            <a class="collapse-item" href="#">Purchase order</a>
            <a class="collapse-item" href="/suppliercredit">Supplier credit</a>
            <a class="collapse-item" href="#">Credit card credit</a>
            <h6 class="collapse-header">Employees</h6>
            <a class="collapse-item" href="#">Single time activity</a>
            <a class="collapse-item" href="#">Weekly timesheet</a>
            <h6 class="collapse-header">Other</h6>
            <a class="collapse-item" href="#">Bank deposit</a>
            <a class="collapse-item" href="#">Transfer</a>
            <a class="collapse-item" href="#">Journal entry</a>
            <a class="collapse-item" href="#">Statement</a>
            <a class="collapse-item" href="#">Inventory qty adjustment</a>
            <a class="collapse-item" href="#">Paydown credit card</a>
          </div>
        </div>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Main
      </div>

      <!-- Nav Item - Dashboard Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link" href="/home">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>

      <!-- Nav Item - Expenses Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExpenses" aria-expanded="true" aria-controls="collapseExpenses">
          <i class="fas fa-fw fa-receipt"></i>
          <span>Expenses</span>
        </a>
        <div id="collapseExpenses" class="collapse" aria-labelledby="headingExpenses" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/expenses">Expenses</a>
            <a class="collapse-item" href="/suppliers">Suppliers</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Sales Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSales" aria-expanded="true" aria-controls="collapseSales">
          <i class="fas fa-fw fa-file-invoice-dollar"></i>
          <span>Sales</span>
        </a>
        <div id="collapseSales" class="collapse" aria-labelledby="headingSales" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/sales">All Sales</a>
            <a class="collapse-item" href="/invoices">Invoices</a>
            <a class="collapse-item" href="/customers">Customers</a>
            <a class="collapse-item" href="/products">Products and Services</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Journal Entries Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseJournalEntries" aria-expanded="true" aria-controls="collapseJournalEntries">
          <i class="fas fa-fw fa-book"></i>
          <span>Journal Entries</span>
        </a>
        <div id="collapseJournalEntries" class="collapse" aria-labelledby="headingJournalEntries" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/journal_entries">All Journal Entries</a>
            <a class="collapse-item" href="/documents">Documents</a>
            <a class="collapse-item" href="/accounts">Account Titles</a>
            <a class="collapse-item" href="/subsidiary_ledgers">Subsidiary Ledgers</a>
            <a class="collapse-item" href="/report_line_items">Report Line Items</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Users Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUsers" aria-expanded="true" aria-controls="collapseUsers">
          <i class="fas fa-fw fa-users"></i>
          <span>Company Users</span>
        </a>
        <div id="collapseUsers" class="collapse" aria-labelledby="headingUsers" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/company_users">All Company Users</a>
            <a class="collapse-item" href="/roles">Roles</a>
            <a class="collapse-item" href="/abilities">Abilities</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Charts -->
      <li class="nav-item">
        <a class="nav-link" href="/employees">
          <i class="fas fa-fw fa-people-carry"></i>
          <span>Employees</span></a>
      </li>

      <!-- Nav Item - Charts -->
      <li class="nav-item">
        <a class="nav-link" href="/reports">
          <i class="fas fa-fw fa-file-alt"></i>
          <span>Reports</span></a>
      </li>

      <!-- Nav Item - Charts -->
      <li class="nav-item">
        <a class="nav-link" href="/taxes">
          <i class="fas fa-fw fa-landmark"></i>
          <span>Taxes</span></a>
      </li>

      <!-- Nav Item - Accounting Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccounting" aria-expanded="true" aria-controls="collapseAccounting">
          <i class="fas fa-fw fa-calculator"></i>
          <span>Accounting</span>
        </a>
        <div id="collapseAccounting" class="collapse" aria-labelledby="headingAccounting" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/reports/chart_of_accounts">Chart of Accounts</a>
            <a class="collapse-item" href="/reconcile">Reconcile</a>
          </div>
        </div>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Others
      </div>

      <!-- Nav Item - Companies Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCompanies" aria-expanded="true" aria-controls="collapseCompanies">
          <i class="far fa-fw fa-building"></i>
          <span>Companies</span>
        </a>
        <div id="collapseCompanies" class="collapse" aria-labelledby="headingCompanies" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="/current_company">Current Company</a>
            <a class="collapse-item" href="/companies">List</a>
            <a class="collapse-item" href="/applications">Applications</a>
          </div>
        </div>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Search -->
          <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search"
            method="GET" action="/search"
          >
            @csrf
            <div class="input-group">
              <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2" name="search">
              <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                  <i class="fas fa-search fa-sm"></i>
                </button>
              </div>
            </div>
          </form>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
              <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
              </a>
              <!-- Dropdown - Messages -->
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                  @csrf
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>

            <!-- Nav Item - Alerts -->
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">{{ \Auth::user()->unreadNotifications->count() }}</span>
              </a>
              <!-- Dropdown - Alerts -->
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  Alerts Center
                </h6>
                @if(!empty(\Auth::user()->notifications->chunk(3)->first()))
                    @foreach(\Auth::user()->notifications->chunk(3)->first()->flatten() as $notification)
                      <a class="dropdown-item d-flex align-items-center" href="{{ $notification->data['link'] }}">
                        <div class="mr-3">
                          <div class="icon-circle bg-primary">
                            <i class="{{ $notification->data['class'] }}"></i>
                          </div>
                        </div>
                        <div>
                          <div class="small text-gray-500">{{ $notification->created_at }}</div>
                          {{ $notification->data['message'] }}
                        </div>
                      </a>
                    @endforeach
                @else
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <span class="font-weight-bold">There are no new notifications.</span>
                  </a>
                @endif
                <a class="dropdown-item text-center small text-gray-500" href="/notifications">Show All Alerts</a>
              </div>
            </li>

            <!-- Nav Item - Messages -->
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-danger badge-counter">{{ \Auth::user()->newThreadsCount() }}</span>
              </a>
              <!-- Dropdown - Messages -->
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                  Message Center
                </h6>
                <?php $threads = \Cmgmyr\Messenger\Models\Thread::forUser(Auth::id())->latest('updated_at')->get(); ?>
                @if(!empty($threads->chunk(4)->first()))
                  @foreach($threads->chunk(4)->first()->flatten() as $thread)
                    <?php $class = $thread->isUnread(Auth::id()) ? 'font-weight-bold' : 'text-truncate'; ?>
                    <a class="dropdown-item d-flex align-items-center" href="/messages/{{ $thread->id }}">
                      <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="{{ asset('img/user.png') }}" alt="">
                        <div class="status-indicator bg-success"></div>
                      </div>
                      <div>
                        <div class="{{ $class }}">{{ $thread->latestMessage->body }}</div>
                        <div class="small text-gray-500">{{ $thread->creator()->name }} · {{ $thread->latestMessage->created_at->diffForHumans() }}</div>
                      </div>
                    </a>
                  @endforeach
                @else
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <span class="font-weight-bold">There are no new messages.</span>
                  </a>
                @endif
                <a class="dropdown-item text-center small text-gray-500" href="/messages">Read More Messages</a>
              </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle" src="{{ asset('img/user.png') }}">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        @yield('content')

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; InnoBooks 2020</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <!-- Core plugin JavaScript-->
  <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

  <!-- Custom scripts for all pages-->
  <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

  <!-- Page level plugins -->
  <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>

  <!-- Page level custom scripts -->
  <script src="{{ asset('js/demo/chart-area-demo.js') }}"></script>
  <script src="{{ asset('js/demo/chart-pie-demo.js') }}"></script>

</body>

</html>
