=== Bookings ===
Contributors: zingiri
Donate link: http://www.zingiri.net/donations
Tags: booking, schedule, scheduler, appointment, reservation, appointment, availability, availability calendar, Booking calendar, booking form, calendar, event calendar, events, reservation plugin, scheduling
Requires at least: 2.1.7
Tested up to: 3.2.1
Stable tag: 1.0.6

Bookings is a powerful reservations scheduler.

Whether you’re running a Beauty salon, Spa, Hairdresser, Massage therapy, Acupuncture or providing hotel rooms, visitors to your site will be able to check availability of your service and make online bookings.

== Description ==

This WordPress plugin allows you to provide online booking services for your site.

* Blackout times are easy to add and manage to restrict reservations.
* Reservation minimum and maximum time limits can be set on a per-resource basis.
* Administrator has power to add and manage an unlimited number of resources. All which can be toggled active and inactive at any point.
* Administrator has control over all reservations and can browse, modify and delete any reservation in the system.
* Administrators can search through the reservation database with a very flexible search tool. Search results can be viewed as HTML, plain-text, XML or CSV.
* Calendars can be viewed in a day/week/month layout.
* Supports multiple languages: English (US & GB), German, French, Spanish, Italian, Hungarian, Dutch, Finnish, Swedish and Turkish.

The Pro version of the plugin additionaly offers:

* Unlimited bookings
* Unlimited schedules
* Possibility to search reservations by reservation ID
* Customization of the booking form (coming soon)

== Installation ==

1. Upload the `bookings` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Include the code [bookings] in any page to display the bookings form. There are differrent options for formatting the bookings form that are documented on [our site](http://www.zingiri.net/plugins-and-addons/bookings#usage "our site").

Please visit [Zingiri](http://www.zingiri.net/plugins-and-addons/bookings/#installation "Zingiri") for more information and support.

== Frequently Asked Questions ==

Please visit the [Zingiri Support Forums](http://forums.zingiri.net/forumdisplay.php?fid=60 "Zingiri Support Forum") for more information and support.

== Screenshots ==

Screenshots will be coming soon [here](http://www.zingiri.net/plugins-and-addons/bookings/ "screenshots").

== Changelog ==

= 1.0.6 =
* Added dashboard view showing various statistics on resources and reservations
* Upgraded HttpRequest class to v1.10.02
* Added license key field for Pro features
* Days before today's date are now protected in calendar
* Added highlight of today's date in calendar

* Fixed encoding issue with Spanish and Danish language files

= 1.0.5 =
* Added new booking templates

= 1.0.4 =
* Removed obsolete cc_footer() function

= 1.0.3 =
* Fixed permissions issue for guest bookings
* Fixed issue with German language not working (encoding issue)
* Added 6 hours and 8 hours timespans

= 1.0.2 = 
* Added possibility to choose the language/locale
* Added display of reservation time in confirmation message
* Fixed issue with sending of confirmation email
* Fixed issue with minimum and maximum booking notice not being taken into account in the calendar
* Fixed issue with settings not updating properly

= 1.0.1 =
* Fixed issue with guest bookings causing a database error
* Fixed issue with multiple bookings by same anonymous user not registering

= 1.0.0 =
* Fixed issue with guest bookings throwing an HTTP 500 error on the site
* Changed menu capability level from 'administrator' to 'edit_plugins'

= 0.9.3 =
* Removed references to WHMCS Bridge in admin notices
* Forced display name as first name and/or last name when those are not specified
* Disabled check on sessions save path

= 0.9.2 =
* Fixed issue with installations running PHP 5.2 or lower
* Updated documentation and support links

= 0.9.1 =
* Beta release

= 0.9.0 =
* Alpha release