<x-mail::message>
@if (isset($content))
<div class="content-body">
@if (!isset($contentMarkdown) || (isset($contentMarkdown) && $contentMarkdown))
{{ Illuminate\Mail\Markdown::parse($content) }}
@else
{{ $content }}
@endif
</div>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
