<?php
/**
Copyright 2013-2014 Bart Verheyde, Nick Korbel
bart.verheyde@ugent.be
This file is not part of default Booked Scheduler.

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
*/

$conf['settings']['cas.version'] = getenv('BOOKED_CAS_SETTINGS_CAS_VERSION');					// '1.0' = CAS_VERSION_1_0, '2.0 = CAS_VERSION_2_0, 'S1' = SAML_VERSION_1_1
$conf['settings']['cas.server.hostname'] = getenv('BOOKED_CAS_SETTINGS_CAS_SERVER_HOSTNAME');		// the hostname of the CAS server
$conf['settings']['cas.port'] = getenv('BOOKED_CAS_SETTINGS_CAS_PORT');						// the port the CAS server is running on
$conf['settings']['cas.server.uri'] = getenv('BOOKED_CAS_SETTINGS_CAS_SERVER_URI');					// the URI the CAS server is responding on
$conf['settings']['cas.change.session.id'] = getenv('BOOKED_CAS_SETTINGS_CAS_CHANGE_SESSION_ID');		// Allow phpCAS to change the session_id
$conf['settings']['email.suffix'] = getenv('BOOKED_CAS_SETTINGS_CAS_EMAIL_SUFFIX');		// Email suffix to use when storing CAS user account. IE, email addresses will be saved to Booked Scheduler as username@yourdomain.com

$conf['settings']['cas_logout_servers'] = getenv('BOOKED_CAS_SETTINGS_CAS_LOGOUT_SERVERS');				// Comma separated list of servers to use for logout. Leave blank to not use cas logout servers

$conf['settings']['cas.certificates'] = getenv('BOOKED_CAS_SETTINGS_CERTIFICATES');	// Path to certificate to use for CAS. Leave blank if no certificate should be used
$conf['settings']['cas.attribute.mapping'] = getenv('BOOKED_CAS_SETTINGS_CAS_ATTRIBUTE_MAPPING');  //bookedAttribute=CASAttribute
$conf['settings']['cas.debug.enabled'] = getenv('BOOKED_CAS_SETTINGS_CAS_DEBUG_ENABLED');
$conf['settings']['cas.debug.file'] = getenv('BOOKED_CAS_SETTINGS_CAS_DEBUG_FILE');
?>
