# PHP-ACE #

PHP interface to ACE

http://sweaglesw.org/linguistics/ace/

### Example Usage ###

```php
$ace = new PHPACE(
  '/path/to/ace-0.9.13/ace',
  '/path/to/erg-1111-x86-64-0.9.13.dat'
);
$result = $ace->parseSentence('He gave her cat food.');
var_dump($result);

/*
SENT: He gave her cat food.
[ LTOP: h0
INDEX: e2 [ e SF: prop TENSE: past MOOD: indicative PROG: - PERF: - ]
RELS: < [ pron_rel<0:2> LBL: h4 ARG0: x3 [ x PERS: 3 NUM: sg GEND: m PRONTYPE: std_pron ] ]
 [ pronoun_q_rel<0:2> LBL: h5 ARG0: x3 RSTR: h6 BODY: h7 ]
 [ "_give_v_1_rel"<3:7> LBL: h1 ARG0: e2 ARG1: x3 ARG2: x8 [ x PERS: 3 NUM: sg ] ARG3: x9 [ x PERS: 3 NUM: sg IND: + ] ]
 [ def_explicit_q_rel<8:11> LBL: h10 ARG0: x9 RSTR: h11 BODY: h12 ]
 [ poss_rel<8:11> LBL: h13 ARG0: e14 [ e SF: prop TENSE: untensed MOOD: indicative PROG: - PERF: - ] ARG1: x9 ARG2: i15 [ i PERS: 3 NUM: sg GEND: f PRONTYPE: std_pron ] ]
 [ pronoun_q_rel<8:11> LBL: h16 ARG0: i15 RSTR: h17 BODY: h18 ]
 [ pron_rel<8:11> LBL: h19 ARG0: i15 ]
 [ "_cat_n_1_rel"<12:15> LBL: h13 ARG0: x9 ]
 [ udef_q_rel<16:21> LBL: h20 ARG0: x8 RSTR: h21 BODY: h22 ]
 [ "_food_n_1_rel"<16:21> LBL: h23 ARG0: x8 ] >
HCONS: < h0 qeq h1 h6 qeq h4 h11 qeq h13 h17 qeq h19 h21 qeq h23 > ]
*/

array(4) {
  ["LTOP"]=>
  string(2) "h0"
  ["INDEX"]=>
  array(7) {
    ["root"]=>
    string(2) "e2"
    ["child"]=>
    string(1) "e"
    ["SF"]=>
    string(4) "prop"
    ["TENSE"]=>
    string(4) "past"
    ["MOOD"]=>
    string(10) "indicative"
    ["PROG"]=>
    string(1) "-"
    ["PERF"]=>
    string(1) "-"
  }
  ["RELS"]=>
  array(10) {
    [0]=>
    array(5) {
      ["label"]=>
      string(8) "pron_rel"
      ["offset_start"]=>
      int(0)
      ["offset_end"]=>
      int(2)
      ["LBL"]=>
      string(2) "h4"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(5) {
          ["root"]=>
          string(2) "x3"
          ["child"]=>
          string(1) "x"
          ["PERS"]=>
          string(1) "3"
          ["GEND"]=>
          string(1) "m"
          ["NUM"]=>
          string(2) "sg"
        }
      }
    }
    [1]=>
    array(7) {
      ["label"]=>
      string(13) "pronoun_q_rel"
      ["offset_start"]=>
      int(0)
      ["offset_end"]=>
      int(2)
      ["LBL"]=>
      string(2) "h5"
      ["RSTR"]=>
      string(2) "h6"
      ["BODY"]=>
      bool(false)
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(2) "x3"
        }
      }
    }
    [2]=>
    array(5) {
      ["label"]=>
      string(15) ""_give_v_1_rel""
      ["offset_start"]=>
      int(3)
      ["offset_end"]=>
      int(7)
      ["LBL"]=>
      string(2) "h1"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(4) {
          ["root"]=>
          string(2) "e2"
          ["child"]=>
          string(1) "x"
          ["PERS"]=>
          string(1) "3"
          ["NUM"]=>
          bool(false)
        }
      }
    }
    [3]=>
    array(7) {
      ["label"]=>
      string(18) "def_explicit_q_rel"
      ["offset_start"]=>
      int(8)
      ["offset_end"]=>
      int(11)
      ["LBL"]=>
      string(3) "h10"
      ["RSTR"]=>
      string(3) "h11"
      ["BODY"]=>
      bool(false)
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(2) "x9"
        }
      }
    }
    [4]=>
    array(5) {
      ["label"]=>
      string(8) "poss_rel"
      ["offset_start"]=>
      int(8)
      ["offset_end"]=>
      int(11)
      ["LBL"]=>
      string(3) "h13"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(7) {
          ["root"]=>
          string(3) "e14"
          ["child"]=>
          string(1) "e"
          ["SF"]=>
          string(4) "prop"
          ["TENSE"]=>
          string(8) "untensed"
          ["MOOD"]=>
          string(10) "indicative"
          ["PROG"]=>
          string(1) "-"
          ["PERF"]=>
          string(1) "-"
        }
      }
    }
    [5]=>
    array(7) {
      ["label"]=>
      string(13) "pronoun_q_rel"
      ["offset_start"]=>
      int(8)
      ["offset_end"]=>
      int(11)
      ["LBL"]=>
      string(3) "h16"
      ["RSTR"]=>
      string(3) "h17"
      ["BODY"]=>
      bool(false)
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(3) "i15"
        }
      }
    }
    [6]=>
    array(5) {
      ["label"]=>
      string(8) "pron_rel"
      ["offset_start"]=>
      int(8)
      ["offset_end"]=>
      int(11)
      ["LBL"]=>
      string(3) "h19"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(3) "i15"
        }
      }
    }
    [7]=>
    array(5) {
      ["label"]=>
      string(14) ""_cat_n_1_rel""
      ["offset_start"]=>
      int(12)
      ["offset_end"]=>
      int(15)
      ["LBL"]=>
      string(3) "h13"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(2) "x9"
        }
      }
    }
    [8]=>
    array(7) {
      ["label"]=>
      string(10) "udef_q_rel"
      ["offset_start"]=>
      int(16)
      ["offset_end"]=>
      int(21)
      ["LBL"]=>
      string(3) "h20"
      ["RSTR"]=>
      string(3) "h21"
      ["BODY"]=>
      bool(false)
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(2) "x8"
        }
      }
    }
    [9]=>
    array(5) {
      ["label"]=>
      string(15) ""_food_n_1_rel""
      ["offset_start"]=>
      int(16)
      ["offset_end"]=>
      int(21)
      ["LBL"]=>
      string(3) "h23"
      ["ARGN"]=>
      array(1) {
        ["ARG0"]=>
        array(1) {
          ["root"]=>
          string(2) "x8"
        }
      }
    }
  }
  ["HCONS"]=>
  array(15) {
    [0]=>
    string(2) "h0"
    [1]=>
    string(3) "qeq"
    [2]=>
    string(2) "h1"
    [3]=>
    string(2) "h6"
    [4]=>
    string(3) "qeq"
    [5]=>
    string(2) "h4"
    [6]=>
    string(3) "h11"
    [7]=>
    string(3) "qeq"
    [8]=>
    string(3) "h13"
    [9]=>
    string(3) "h17"
    [10]=>
    string(3) "qeq"
    [11]=>
    string(3) "h19"
    [12]=>
    string(3) "h21"
    [13]=>
    string(3) "qeq"
    [14]=>
    string(3) "h23"
  }
}

```
