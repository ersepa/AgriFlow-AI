@php
    $routeDecision = session(
        'route_decision',
        []
    );
@endphp

@if(!empty($routeDecision))
    @include(
        'shared.step52-route-summary',
        [
            'routeDecision' =>
                $routeDecision
        ]
    )
@endif
