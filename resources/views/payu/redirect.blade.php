<h2>Redirecting to PayU...</h2>


<form action="{{ $posted['action'] }}" method="post" name="payuForm">
    @foreach($posted as $key => $value)
        @if($key !== 'action')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
</form>


<script>
    document.payuForm.submit();
</script>
