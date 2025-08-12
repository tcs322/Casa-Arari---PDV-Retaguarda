<style>
.breadcrumbul ul li {
    display: inline-block;
    padding-right: 4px;
}

.breadcrumbul a {
    color: rgb(59, 113, 202);
}
</style>
@unless ($breadcrumbs->isEmpty())
<div class="breadcrumbul">
    <ul class="breadcrumb text-end align-content-end">
        @foreach ($breadcrumbs as $breadcrumb)

            @if (!is_null($breadcrumb->url) && !$loop->last)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
            @endif

            @if (!$loop->last)
                &rarr;
            @endif

        @endforeach
    </ul>
</div>

@endunless
