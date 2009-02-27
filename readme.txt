=== Kiva ===
Contributors: davidjmillerorg, cboyack
Tags: external tool integration, kiva, giving, charity, philanthropy, loans
Requires at least: 1.5
Tested up to: 2.7
Stable tag: trunk

Kiva lets you give a little to help people worldwide. The Kiva plugin lets you raise the visibility of Kiva by displaying loans with a link donate.

== Description ==
For those who wish to help others but who have limited means to do so Kiva provides an opportunity to give a little (as little as $25) and help people around the world who have business plans to lift themselves up economically. Loans from individuals get pooled as necessary to fund people. I have been very impressed with this system that allows people to help others by using small means to make great things happen.

I was very happy to take the opportunity to take the code written by Connor Boyack and turn it into the Kiva plugin for Wordpress so that people can raise the visibility of Kiva by displaying loans in the fundraising stage with a link donate.

The plugin options allow you to choose how many loans to show. You may also choose to display text information, an image, or both for the loans.

Options include:

* Number of posts to show.
* Display format for loan list - Image only displays the image for each loan linked to the donation page, Both displays the image and text information, Text only displays name(linked to the donation page), business, country, and fundraising level/goal for the loan.
* Gender - you can restrict to only show loans for men or loans for women.
* Region - you can show loans only in one of 7 geographic regions.
* Sector - you can only show certain types of loans such as retail or agriculture.

The plugin allows for the function call anywhere in your page templates:

* `<?php show_kiva(); ?>`

If you use widgets, you can place this shortcode in a text widget:

* `[SHOW-KIVA]`

== Installation ==

To install it simply unzip the file linked above and save it in your plugins directory under wp-content. In the plugin manager activate the plugin. Settings for the plugin may be altered under the Kiva page of the Options menu (version 2.3) or Settings menu (version 2.5 or later).

== Frequently Asked Questions ==

== Screenshots ==

1. This is a sample options page displayed in Wordpress 2.7

2. This is a sample of the image only output from Kiva

3. This is a sample of the image and text output from Kiva

4. This is a sample of the text only output from Kiva