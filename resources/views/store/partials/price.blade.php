<span class="st-price">{{ $price->currency_symbol }}{{ number_format($price->sale_price, 2) }}</span>
@if($price->sale_price < $price->mrp)
    <span class="st-mrp">{{ $price->currency_symbol }}{{ number_format($price->mrp, 2) }}</span>
    <span class="st-off">{{ $price->discount_percent }}% off</span>
@endif
