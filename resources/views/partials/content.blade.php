<div class="container">
    @php ($data = json_decode($params))
    <div class="detail-block">
        <div class="row content-title">
            <h1>{{ $data->page_title }}</h1>
        </div>
        <div class="content-body">
            <p>{!! $data->page_content !!}</p>
        </div>
    </div>
</div>
