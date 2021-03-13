# FreeSWITCH
Add this to your default dialplan:
```XML
<extension name="SpacePhone ENUM">
    <condition field="destination_number" expression="^(\+31|0031|0)(9799\d\d)(\d*)$">
        <action application="set" data="continue_on_fail=true"/>
        <action application="enum" data="sip:+31$2 e164.spacephone.org"/>
        <action application="bridge" data="${enum_auto_route}"/>
        <action application="playback" data="tone_stream://%(330,15,950);%(330,15,1400);%(330,1000,1800);loops=2"/>
        <action application="playback" data="ivr/ivr-unallocated_number.wav"/>
        <action application="playback" data="tone_stream://%(330,15,950);%(330,15,1400);%(330,1000,1800);loops=2"/>
    </condition>
</extension>
```
