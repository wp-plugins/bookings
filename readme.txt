=== Bookings ===
Contributors: zingiri
Donate link: http://www.zingiri.com/donations
Tags: booking, schedule, scheduler, appointment, reservation, appointment, availability, availability calendar, Booking calendar, booking form, calendar, event calendar, events, reservation plugin, scheduling
Requires at least: 2.1.7
Tested up to: 3.2.1
Stable tag: 1.3.0

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
* Supports multiple languages: English (US & GB), German, French, Spanish, Italian, Hungarian, Dutch, Finnish, Swedish, Turkish, Arabic, Bulgarian, Chinese (Traditional & Simplified), Czech, Danish, Greek, Japanese, Korean, Polish, Portuguese, Slovak and Slovenian.
* Community support via our [Forums](http://forums.zingiri.net "Forums") 

The Pro version of the plugin additionaly offers:

* Unlimited bookings
* Unlimited schedules
* Customization of the booking form with custom fields
* Export reservations to your favorite calendar (Outlook, iCal, etc) from the admin back-end or your confirmation email
* Possibility to search reservations by reservation ID
* Priority support via our [Helpdesk](http://www.zingiri.com/portal "Helpdesk")

And coming soon in the Pro version:

* Integration of payment gateways such as Paypal etc 
* Mobile syncing with your iPhone, iPad, Android device and more
* Mutliple configuration options: send admin confirmation email, reservations require confirmation (or not), etc
* Possibility to edit the thank you text on the confirmation page
* Possibility to edit the confirmation emails 

== Installation ==

1. Upload the `bookings` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Include the code [bookings] in any page to display the bookings form. There are differrent options for formatting the bookings form that are documented on [our site](http://www.zingiri.com/bookings#usage "our site").

Please visit [Zingiri](http://www.zingiri.com/bookings/#installation "Zingiri") for more information and support.

== Frequently Asked Questions ==

Please visit the [Zingiri Support Forums](http://forums.zingiri.net/forumdisplay.php?fid=60 "Zingiri Support Forum") for more information and support.

= Available front-end style classes =

.bookingsCalendarBody : class for div containing the calendar

== Screenshots ==

Screenshots will be coming soon [here](http://www.zingiri.com/bookings/ "screenshots").

== Changelog ==

= 1.3.0 =
* Added possibility to define minimum time between bookings for each resource
* Added possibility to define email templates and override the default text
* Fixed issue with double body tag appearing on some pages
* Always show registration form, even for registered Wordpress users
* Updated Italian language translations
* Removed wrapping tables styling
* Changed bookings template=link parameter, 'resource' should be specified instead of 'schedule'
* Fixed 'per page' and 'cancel' links on resource page
* Fixed issue with captcha element on forms not working properly

= 1.2.0 =
* Split back-end roles in admin and user roles
* Added possibility to select required capability for admins and users
* Own reservations are now highlighted in a different color in front end
* Translated booking registration page
* Replaced [...] by 'Available' for English GB and US languages
* Added 45 minutes schedule time span
* Fixed issue with disappearing user name in front end calendar
* Changed header date format for English GB so that day appears before month
* Updated readme
* Allow navigation on days before today in back-end calendar
* Fixed Brazilian Portuguese language encoding
* Added possibility to export reservation to calendar in ICS or VCS format (Pro)
* Added 'Add to calendar' link on emails (Pro)

= 1.1.2 =
* Fixed release issue

= 1.1.1 =
* Removed Accessories and Participants tabs from reservation pop-up (back-end) as currently not used
* Fixed issue with pop-up windows not being styled properly
* Fixed issue with Change Name not working when editing a reservation in the back-end
* Fixed encoding issues with Slovenian and Slovak languages
* Fixed issue with booking confirmation not showing when default permalinks set (Pro version)

= 1.1.0 =
* Use Wordpress time settings for calendar time settings (military time vs am/pm notation)
* Fixed issue with Polish, Czech and French language files encoding
* Show registration form in all cases
* Added possibility to edit custom booking form (Pro version)

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
* Removed wrong plugin name in admin notices
* Forced display name as first name and/or last name when those are not specified
* Disabled check on sessions save path

= 0.9.2 =
* Fixed issue with installations running PHP 5.2 or lower
* Updated documentation and support links

= 0.9.1 =
* Beta release

= 0.9.0 =
* Alpha release