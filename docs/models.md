
#### Associação
```
--patch out model default app/models
--fields of model
-s, --assosiacao[=ASSOSIACAO]
-r [Service Name]

php adcconsole/console app:model:create Customer  -f nome,sobre -p teste -s contacts
```


#### Composicao
```
--patch out model default app/models
--fields of model
-c, --composition[=COMPOSITION]
-r [Service Name]


php adcconsole/console app:model:create Customer  --fields=nome,sobre --patch=teste -c contacts
```


#### Agregação
```
--patch out model default app/models
--fields of model
 --pivot[=PIVOT] default $name+$agregate
-a, --aggregate[=AGGREGATE]
-r [Service Name]

php adcconsole/console app:model:create Customer  --fields=nome,sobre --patch=teste  -a contacts 
```

