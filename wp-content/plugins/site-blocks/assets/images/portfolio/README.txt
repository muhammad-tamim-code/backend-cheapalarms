Portfolio / client logo strip (Safeguard homepage)



Heading copy (per Nafizul): "Brands we have worked with", not "clients" unless you have written permission.



USAGE RIGHTS

- Confirm logo usage with each brand (or your contract scope) before publishing on the live site.

- Only verified artwork is shown on the homepage marquee (see inc/safeguard-portfolio-brands.php).

- Logos here are for development and internal review until legal sign-off.



VERIFIED ON SITE (8), manually checked Jul 2026

  woolworths, kfc, australia-post, nab, storageplus, zone-bowling, jas-forwarding, specific-freight



HIDDEN UNTIL OFFICIAL ARTWORK (7)

  rathdrum-properties, fetched file is blank/black

  sbm, wrong entity from sbm.com.au

  freechoice, auto-fetch returned WordPress favicon (file removed)

  timezone, auto-fetch returned "T2" logo, not Timezone

  kingpin, favicon does not match Kingpin Bowling brand

  bp, zambrero, text SVG placeholders only



RE-FETCH (PowerShell, from repo root):

  powershell -File wordpress/wp-content/plugins/site-blocks/scripts/fetch-portfolio-logos.ps1



After adding/replacing a file, set verified => true in safeguard-portfolio-brands.php.



MISSING / REPLACE WITH OFFICIAL ASSETS

- Freechoice, freechoice.com.au brand kit

- BP, bp.com.au

- Zambrero, zambrero.com.au

- Rathdrum Properties, rathdrum.com.au

- SBM, confirm correct company + logo with client

- Timezone, timezonegames.com brand asset

- Kingpin, kingpinbowling.com.au brand asset

