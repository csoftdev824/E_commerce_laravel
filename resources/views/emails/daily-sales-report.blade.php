Daily Sales Report - {{ $date }}

Dear Admin,

Here is the sales report for {{ $date }}:

@if(count($salesData) > 0)
@foreach($salesData as $sale)
Product: {{ $sale['product_name'] }}
Quantity Sold: {{ $sale['quantity_sold'] }}
Revenue: ${{ number_format($sale['revenue'], 2) }}

@endforeach
@else
No sales were made today.
@endif

Thank you.
