=== Bookings ===
Contributors: zingiri
Donate link: http://www.zingiri.com/donations
Tags: booking, schedule, scheduler, appointment, reservation, appointment, availability, availability calendar, Booking calendar, booking form, calendar, event calendar, events, reservation plugin, scheduling
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.5.6

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
* Reservations can be set to be approved or not
* Supports multiple languages: English (US & GB), German, French, Spanish, Italian, Hungarian, Dutch, Finnish, Swedish, Turkish, Arabic, Bulgarian, Chinese (Traditional & Simplified), Czech, Danish, Greek, Japanese, Korean, Polish, Portuguese, Slovak and Slovenian.
* Community support via our [Forums](http://forums.zingiri.net "Forums") 

The Pro version of the plugin additionaly offers:

* Unlimited bookings
* Unlimited schedules
* Integration of payment gateways such as Paypal etc 
* Products & services
* Customization of the booking form with custom fields
* Mobile syncing with your iPhone, iPad, Android device and more
* Possibility to edit the thank you text on the confirmation page
* Possibility to edit the confirmation emails 
* Multiple configuration options: send admin confirmation email, currency, etc
* Export reservations to your favorite calendar (Outlook, iCal, etc) from the admin back-end or your confirmation email
* Possibility to search reservations by reservation ID
* Possibility to define your own time spans
* Priority support via our [Helpdesk](http://www.zingiri.com/portal "Helpdesk")

== Installation ==

1. Upload the `bookings` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Include the code [bookings] in any page to display the bookings form. There are differrent options for formatting the bookings form that are documented on [our site](http://www.zingiri.com/bookings#usage "our site").

Please visit [Zingiri](http://wiki.zingiri.com/index.php?title=Bookings:Main "Zingiri") for more information and support.

== Frequently Asked Questions ==

Please visit the [Zingiri Support Forums](http://forums.zingiri.net/forumdisplay.php?fid=60 "Zingiri Support Forum") or [Wiki](http://wiki.zingiri.com/index.php?title=Bookings:Main "Wiki") for more information and support.

= Available front-end style classes =

.bookingsCalendarBody : class for div containing the calendar

== Screenshots ==

Screenshots are available [here](http://www.zingiri.com/plugins-and-addons/bookings/ "screenshots").

== Changelog ==

= 1.5.6 =
* Allow unlicensed users access to Products & Services feature
* Added product details to reservation summary
* Fixed issue with first name and last name not being prefilled correctly for logged in users
* Added [PRODUCT] template tag for emails and confirmation messages
* Added [CLIENTNAME] template tag for emails and confirmation messages
* Disabled properties of system fields in form editor
* Fixed issue with change of date on template schedule2 not working in some cases
* Fixed issue with templates using daystoshow not showing reservations on days in the future
* Updated German language prompts

= 1.5.5 =
* Added possibility to define custom time spans
* Fixed issue with +/- buttons on product form not working
* Updated documentation and support links
* Fixed issue with product time spans not working in certain cases
* In products/resources templates, the sort order is now product name and then resource name
* Removed date fromto element from forms

= 1.5.4 =
* Fixed issue with 'link' template
* Removed obsolete css/ui-lightness folder
* Removed obsolete css/jscalendar folder
* Removed obsolet js/jscalendar folder
* Don't display unselected resources in reservation overview
* Replaced inline color styles by classes on front end reservation calendar
* Fixed issue with last name being prefilled with a question mark on registration form
* Fixed issue with 'A' beging defaulted in form fields

= 1.5.3 =
* Fixed issue with calendar.setup javascript
* Fixed issue with missing load of jQuery UI Sortable

= 1.5.2 =
* Replaced admin reservation popup window with jQuery dialog
* Replaced admin user selection and search popup window with jQuery dialog
* Set no bullet points in front end registration form
* Hide inactive resources from front end screens
* Load simple_html_dom class only when required
* Fixed wrong link to login details showing after activating calendar syncing
* Replaced Prototype/Scriptaculous scripts by jQuery
* Use WP default jQuery libraries
* Removed reminder section from blackout pop-up

= 1.5.1 =
* Fixed packaging issue

= 1.5.0 =
* Added 'event mode' allowing to define a schedule active only on certain dates
* Added new template 'schedule1' for the [bookings] short code displaying a calendar with availability for all resources (similar to the schedule calendar in the back-end)
* Added new template 'schedule2' for the [bookings] short code displaying a calendar with the possibility to search by date, showing availability for all resources
* Added new template 'hotel1' for the [bookings] short code tailored for hotels and B&B's
* Added 'daystoshow' parameter for the [bookings] short code allowing to display more than 1 day of availability in the front-end calendar
* Implemented Wordpress short code API, allowing to manage multiple bookings pages and multiple short codes on the same page
* Added reservation details on checkout page
* Fixed issue with selecting Japanese language
* Added new colors.css style sheet with the colors of the reservation statuses
* Fixed issue where when clicking 'Add' to add another product or service after having added one, the link takes you to the set up screen
* Removed loading of news info in sidebar, providing more space for other functionality
* Included local image files
* Fixed issue with back link when an error occurs creating or modifying a schedule
* Added new styling to front end buttons
* Removed seconds display from time on confirmation messages
* Displayed am/pm in lower case for consistency
* Changed default order of reservations seen by customer to show most recently created first
* Fixed issue with tooltip being offset from mouse position
* Changed Paypal item time to show reservation date instead of reservation id
* Changed Paypal invoice id to show Bookings invoice id
* Moved to same checkout form for both standard and pro users
* Fixed issue with month selector on front end calendar
* Removed showSummary javascript action from front end calendar
* Centered calendar and date jumper on front end
* Show loader icon when saving schedule or resource

= 1.4.3 =
* Fixed issue with redirects not working if PHP sessions not configured properly on client side
* Store http referer in option instead of session variable
* Delete bookings php session when deactivating
* Fixed issue with loading of Norwegian language files
* Fixed issue where Wordpress Editors and some other roles had no access to the front-end booking screens

= 1.4.2 =
* Only load admin javascript and styles on Bookings pages

= 1.4.1 =
* Fixed issue 'Invalid argument supplied for foreach() in /.../wp-content/plugins/bookings/includes/support-us.inc.php on line 51'
* Fixed issue with 'no access' being displayed on Blackouts screen
* Fixed issue with textarea element in templates

= 1.4.0 =
* Added products & services concept
* Added Paypal payment gateway (Pro)
* Added option for customers to view and cancel upcoming reservations (Pro)
* Fixed issue with timezones
* Removed 'sc1' prefix from keys
* Fixed issue with changing schedules in the 'Schedule Calendar'
* Fixed Finnish language encoding

= 1.3.3 =
* Fixed issue with url encoding

= 1.3.2 =
* Added option to send confirmation email to administrator (Pro)
* Added SMTP email settings (Pro) 
* Added approval management
* Improved role management (admin vs operator)
* Added display of region in setup panel
* Improved usability by auto redirecting after admin update
* Removed date link from front end calendar
* Removed grey background from admin screens
* Removed encapsulating html table from admin screens
* Fixed issue with front end month selector not working on some themes
* Centered day headers on front end calendar
* Removed donations reminder

= 1.3.1 =
* Added mobile sync
* Added Norwegian language
* Verified compatibility with WP 3.3
* Added new region (Europe & Africa)
* Updated documentation
* Fixed issue with Edit form link appearing on front end booking form
* Removed table containing reservation details from email when custom emails are being used
* Fixed issue with deleting resources

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