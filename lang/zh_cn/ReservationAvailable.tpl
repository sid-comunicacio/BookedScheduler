{*
Copyright 2011-2018 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*}
{$FirstName},<br/>
目前，{$ResourceName}在{formatdate date=$StartDate key=res_popup}和{formatdate date=$EndDate key=res_popup}之间可被预约。
<br/>
<br/>
<a href="{$ScriptUrl}/{$ReservationUrl}">立即预约</a> |
<a href="{$ScriptUrl}">登录到 CVC Rental</a>