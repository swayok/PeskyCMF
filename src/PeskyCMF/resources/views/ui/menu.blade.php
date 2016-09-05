<ul class="sidebar-menu" id="common-menu">
    <li class="header">{{ \PeskyCMF\Config\CmfConfig::transCustom('.main_menu.header') }}</li>
    @foreach (\PeskyCMF\Config\CmfConfig::getInstance()->menu() as $info)
        @if (!empty($info['submenu']))
            <li class="treeview">
                <a href="@if (empty($info['url']))javascript: void(0)@else{{ $info['url'] }}@endif"
                @if (!empty($info['id'])) id="{{ $info['id'] }}" @endif
                @if (!empty($info['class'])) class="{{ $info['class'] }}" @endif>
                    @if (!empty($info['icon']))<i class="{{ $info['icon'] }}"></i>@endif
                    <span>{!! trans($info['label']) !!}</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @foreach ($info['submenu'] as $subItem)
                    <li>
                        <a href="{{ $subItem['url'] }}"
                        @if (!empty($subItem['id'])) id="{{ $subItem['id'] }}" @endif
                        @if (!empty($subItem['class'])) class="{{ $subItem['class'] }}" @endif>
                            @if (!empty($subItem['icon']))<i class="{{ $subItem['icon'] }}"></i>@endif
                            <span>{!! trans($subItem['label']) !!}</span>{!! array_get($subItem, 'addition', '') !!}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li>
                <a href="@if (empty($info['url']))javascript: void(0)@else{{ $info['url'] }}@endif"
                @if (!empty($info['id'])) id="{{ $info['id'] }}" @endif
                @if (!empty($info['class'])) class="{{ $info['class'] }}" @endif>
                    @if (!empty($info['icon']))<i class="{{ $info['icon'] }}"></i>@endif
                    <span>{!! trans($info['label']) !!}</span>{!! array_get($info, 'addition', '') !!}
                </a>
            </li>
        @endif
    @endforeach
</ul>