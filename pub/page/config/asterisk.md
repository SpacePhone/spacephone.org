# Asterisk
Add this to your (internal) context(s):
```Ini
exten => _X.,1,NoOp("Space phone call to ${EXTEN}")
exten => _X.,2,Set(sipcount=${ENUMLOOKUP(${EXTEN},sip,c,,e164.spacephone.org)})
exten => _X.,3,Set(counter=0)
exten => _X.,4,GotoIf($["${counter}"<"${sipcount}"]?5:7)
exten => _X.,5,Dial(SIP/${ENUMLOOKUP(${EXTEN},sip,,${counter},e164.spacephone.org)})
exten => _X.,6,GotoIf($["${counter}"<"${sipcount}"]?5:7)
exten => _X.,7,NoOp("No valid entries in e164.spacephone.org for ${EXTEN}")
```

# Asterisk, using Asterisk Extension Language (AEL)
Add this to your (internal) context(s):
```Ini
_X. => {
    NoOp("Space phone call to ${EXTEN}");
    sipcount=${ENUMLOOKUP(${EXTEN},sip,c,,e164.spacephone.org)};
    counter=0;
    if (${counter} < ${sipcount}) {
        while (${counter} < ${sipcount}) {
            Dial(SIP/${ENUMLOOKUP(${EXTEN},sip,,${counter},e164.spacephone.org)});
            counter=${counter}+1;
        }
    } else {
        NoOp("No valid entries in e164.spacephone.org for ${EXTEN}");
        Dial(DAHDI/g1/${EXTEN});
    }
};
```
