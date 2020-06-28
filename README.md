


# TYPO3 extension  excelbill_tt_products 

The TYPO3 extension  excelbill_tt_products  has the purpose to enable the generation of a bill in an Excel format.

Put this into the Setup:

## Generate bill:

```
plugin.tt_products {
  bill {
     generation = auto
     type = excel
     handleLib = PhpSpreadsheet
     handleLib {
        template = fileadmin/templates/excel_template.xls
     }
     conf {
        path = fileadmin/data/bill
     }
  }
  orderEmail {
     10002.attachment = bill
  }
}
```

Use the setup bill.conf to overwrite the charset and the standard configuration attributes of TCPDF.
This extension contains a HTML template file which you can move into the fileadmin folder if you want to adapt it to your needs.


```
plugin.tt_products {
  bill.conf {
     templateFile = fileadmin/templates/excel_template.xls
  }
}
```


## PhpSpreadsheet Library:

Use the Extension Manager to set the relative library path to PhpSpreadsheet, where the TYPO3 home directory is the starting point.

```
libraryPath = src/PhpSpreadsheet
```

If you have installed the extension base_excel, then you must use this configuration:

```
libraryPath = typo3conf/ext/base_excel/Vendor/PhpSpreadsheet/src/PhpSpreadsheet/
```


## Contributors:

[Excel icons](https://icon-icons.com/de/symbol/Excel-2010-excel/23622)



