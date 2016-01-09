<ul class="sidebar-menu" id="common-menu">
    <li class="header">{{ trans('cmf::cmf.ui.main_menu_header') }}</li>
    @foreach (\PeskyCMF\Config\CmfConfig::getInstance()->menu() as $info)
        @if (!empty($info['submenu']))
            <li class="treeview">
                <a href="@if (empty($info['url']))javascript: void(0)@else{{ $urlPrefix . $info['url'] }}@endif">
                    @if (!empty($info['icon']))<i class="{{ $info['icon'] }}"></i>@endif
                    <span>{{ trans($info['label']) }}</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @foreach ($info['submenu'] as $subItem)
                    <li>
                        <a href="{{ $urlPrefix }}{{ $subItem['url'] }}">
                            @if (!empty($subItem['icon']))<i class="{{ $subItem['icon'] }}"></i>@endif
                            <span>{{ trans($subItem['label']) }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li>
                <a href="@if (empty($info['url']))javascript: void(0)@else{{ $urlPrefix . $info['url'] }}@endif">
                    @if (!empty($info['icon']))<i class="{{ $info['icon'] }}"></i>@endif
                    <span>{{ trans($info['label']) }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>