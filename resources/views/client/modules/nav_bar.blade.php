<header id="main-header">
    <section class="container-fluid container">
        <section class="row-fluid">
            <section class="span4">
                <h1 id="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('bower_components/book-client-lte/images/logo.png') }}" />
                    </a>
                </h1>
            </section>
            <section class="span8 filter">
                <ul class="top-nav2">
                    <li><a href="{{ route('request') }}">{{ trans('client.list_request') }}</a></li>
                    <li><a href="{{ route('cart') }}">{{ trans('client.cart') }}</a></li>
                </ul>
                <div class="search-bar">
                    <input name="search-client-book" id="search" type="text"
                        value="{{ trans('client.filter_input') }}" />
                </div>
                <div id="data-search" class="box-filter"></div>
            </section>
        </section>
    </section>
    <nav id="nav">
        <div class="navbar navbar-inverse">
            <div class="navbar-inner">
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
                </button>
                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <li> <a href="{{ route('home') }}">{{ trans('book.menu') }}</a> </li>
                        <li class="dropdown">
                            <a class="dropdown-toggle" href="grid-view.html" data-toggle="dropdown">
                                <i class="icon-bell-alt"></i>
                                Notifications:
                                <span class="number-notify-user"></span>
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu notification-data"></ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>
@section('script')
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/search.js') }}" defer>
    </script>
    <script type="text/javascript" src="{{ asset('js/notification_user.js') }}" defer>
    </script>
@endsection
