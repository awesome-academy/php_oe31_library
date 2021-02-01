<section class="top-nav-bar">
    <section class="container-fluid container">
        <section class="row-fluid">
            <section class="span6">
                <ul class="top-nav">
                    <li><a href="{{ route('home') }}" class="active">{{ trans('client.home') }}</a></li>
                </ul>
            </section>
            <section class="span6 e-commerce-list">
                <ul>
                    @if (Auth::check())
                        <li>{{ trans('client.welcome') }} {{ Auth::user()->name }}!
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><a>{{ trans('client.log_out') }}</a></button>
                            </form>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}">{{ trans('client.login') }}</a>
                            or
                            <a href="#">{{ trans('client.create_account') }}</a>
                        </li>
                    @endif
                    <li class="p-category">
                        <a href="{{ route('change-language', ['language' => 'en']) }}">{{ trans('client.en') }}</a>
                        <a
                            href="{{ route('change-language', ['language' => 'vi']) }}">{{ trans('client.vi') }}</a>
                    </li>
                </ul>
                <div class="c-btn"> <a href="{{ route('cart') }}" class="cart-btn">{{ trans('client.cart') }}</a>
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                            {{ trans('client.item_cart') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                        </ul>
                    </div>
                </div>
            </section>
        </section>
    </section>
</section>
