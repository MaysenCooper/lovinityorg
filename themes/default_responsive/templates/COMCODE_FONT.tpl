{+START,IF,{$TAPATALK}}<font face="{$PREG_REPLACE*,.*: (.*);,$1,{FACE}}" color="{$PREG_REPLACE*,.*: (.*);,$1,{COLOR}}" size="{$PREG_REPLACE*,.*: (.*);,$1,{SIZE}}">{CONTENT}</font>{+END}{+START,IF,{$NOT,{$TAPATALK}}}{+START,IF,{$IN_STR,{CONTENT},<div}}<div style="{FACE*} {COLOR*} {SIZE*}">{CONTENT}</div>{+END}{+START,IF,{$NOT,{$IN_STR,{CONTENT},<div}}}<span style="{FACE*} {COLOR*} {SIZE*}">{CONTENT}</span>{+END}{+END}
