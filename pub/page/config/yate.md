# Yate
**Note:** untested.

in yate.conf, enable ENUM
```Ini
[modules]
enumroute.yate=yes
```

enumroute.conf:
```Ini
[general]
priority=20
domains=e164.spacephone.org,e164.arpa
redirect=false
[protocols]
sip=yes
pstn=yes
tel=yes
```
