=== LSX Starter Plugin ===
Contributors: feedmymedia
Donate link: https://www.lsdev.biz/
Tags: lsx
Requires at least: 4.3
Tested up to: 4.8
Requires PHP: 7.0
Stable tag: 1.0.8
License: GPLv3

This module does the following:
===============================
1. If customer purchases the Monthly subscription:
   a. We generate a local coupon with predefined rules.
   (locked to customers email, 1 use, 50% off, expiry date set)
   b. We update the order notes with a message if coupon generation was successful or not.
   c. We mail customer coupon notification email (lsx-coupon-notification-for-woocommerce plugin).
   d. We update the order notes with a message if coupon was mailed to user or not.
2. If customer purchases the Annual subscription:
   a. We generate a remote coupon with predefined rules.
   (locked to customers email, 1 use, 100% off, expiry date set)
   b. We update the order notes with a message if coupon generation was successful or not.
   c. We mail customer coupon notification email (lsx-coupon-notification-for-woocommerce plugin).
   d. We update the order notes with a message if coupon was mailed to user or not.

NOTE: this module uses "automattic/woocommerce" for remote REST API (composer require automattic/woocommerce).