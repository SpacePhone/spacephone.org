# Kamailio and OpenSIPS
**Note:** untested.

in the .cfg file enable ENUM and optionally set domain suffix
```Js
loadmodule "enum.so"
    modparam("enum", "domain_suffix", "e164.spacephone.org.")
```

Usage (run the query):
```Js
# search and rewrite request uri using "e2u+sip" on default domain
enum_query();

# search and rewrite request uri using "e2u+sip" in e164.spacephone.org
enum_query("e164.spacephone.org.");
```
