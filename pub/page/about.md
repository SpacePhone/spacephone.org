# About SpacePhone

You can contact spacephone at [+319799HELP](tel:+3197994375) or
[support@spacephone.org](sip:support@spacephone.org).

## Telephony

SpacePhone utilises national testing prefixes if available in the country,
otherwise we'll resort to a prefix that is not globally routable. This means
your phone numbers are not reachable over public PSTN networks, but also that
the prefix will not collide with existing services.

### Laws and regulations

Reference documents for per-country laws and regulations:

 *  ``+31``, The Netherlands, *Nummerplan telefoon- en ISDN-diensten*
    *  [BWBR0010198]
 *  ``+44``, United Kingdom, *Telecoms numbering*
    *  [Ofcoms telephone numbering]
    *  [Ofcoms numbering site]
 *  ``+49``, Germany, *Nummerierungskonzept*
    *  [Nummerierungskonzept]

## Requirements

There are not many restrictions as to how you implement SpacePhone in your
hacker space, hence _should_ in most points below.

Your hacker space:

 *  should authorise you to request a SpacePhone delegation on behalf of your
    hacker space.
 *  must have its own DNS zone.
 *  must have one or more reachable SIP-addresses.
 *  should accept SIP calls to ``sip:<prefix>@hackerspace.tld``
 *  should route SpacePhone entries in the e164.spacephone.org to the entries
    listed in the SpacePhone zone. For examples, check the [configuration
    examples](/config/).

[BWBR0010198]: http://wetten.overheid.nl/BWBR0010198
[Ofcoms telephone numbering]: http://stakeholders.ofcom.org.uk/telecoms/numbering/
[Ofcoms numbering site]: http://www.ofcom.org.uk/static/numbering/index.htm
[Nummerierungskonzept]: https://www.bundesnetzagentur.de/DE/Sachgebiete/Telekommunikation/Unternehmen_Institutionen/Nummerierung/Nummerierungskonzept/nummerierungskonzept_node.html
