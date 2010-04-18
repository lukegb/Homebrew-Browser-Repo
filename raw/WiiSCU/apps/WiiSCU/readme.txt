WiiSCU v0.21
Copyright (c) 2009 Wack0
Portions copyright (c) 2008 tona
Portions copyright (c) 2008 bushing
PatchMii net_init() retry code copyright (c) 2009 kerframil
Modified by illinialex24

WiiSCU uses PatchMii to update the following to their latest versions:

IOS16
IOS38
IOS60
IOS61
News Channel
Weather Channel
Mii Channel
Photo Channel
Shop Channel


This is my first release. :)
I didn't really know much C# (I know a bit more now), so I
modified IOS51+shop.
(Well, I modified IOS61+shop, but that was based upon IOS51+shop)
So most of the work was done by tona and bushing.
But everyone has to start somewhere ;)

There is a menu-driven interface starting from 0.2, using code from
AnyRegion Changer (tona, you're a life saver !).
+Trucha means 'with the trucha bug'. -Trucha means 'without the trucha
bug' .. but you could figure it out !!

IOS51 support has been removed since 0.21 as, well, who would want an
old shopchannel IOS using up valuable nand space ?

Photo Channel 1.0 support has also been removed since 0.21, as 1.1 is
the only version that recieves updates.

Version history and changelog
`````````````````````````````
v0.23~ Possible fix for Shop Channel update issues
v0.22~ Fixed the IOS60 stub issue for people who have Wii System Menu 4.1 and below
v0.21~ Added code for debug purposes.
     ~ No more 'epic fail' -- now it shows what patchmii returns.
     ~ IOS51 and Photo Channel 1.0 support removed.
     ~ Added an option to exit to the Homebrew Channel.
     ~ News/weather channel now installs HAFx/HAGx as well as
       HAFA/HAGA (using gamedisc region).
     ~ Added sysconf.c to determine region to install news/weather
       channels.
     ~ Tidied up code a bit.
v0.2 ~ patchmii_network_init() now retries a few times if it fails.
     ~ Menu-driven interface integrated into code, installer.c re-written
       completely because of this.
     ~ Photo Channel 1.0 is now supported. There's been no requests for it,
       but I decided to put it in to make the updater "complete".
     ~ New icon by Antiaverage included.
     ..and that's about it !
v0.1 ~ First version, nothing else to say except it had a really irksome
       interface. For IOS51+Shop it was fine, but for an updater that had
       options for 10 titles it would get annoying .. :)