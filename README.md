# Search Query Optimization

## Description
This module is designed to optimize a few things around the "suggested search terms" returned by Magento when typing in the search bar at the top of the page.
The current optimizations are:
- increase default cache size of these results from 100 => 1000
- implements a decay functionality to the `search_query` table (see [Decay Function](#decay-function))


### Decay Function
When a user searches for a term, Magento adds this term to the `search_query` table.
When another search for the same term happens Magento increments the `popularity` of the term.
This allows Magento to return "the most popular" results for the term being searched for.
However Magento does not implement any way of clearing down these search terms automatically, leaving you with a table that only ever grows. Which means the response time will increase (larger table ~= longer response times).

This module runs a cronjob every minute that looks for any records that haven't been used in the last "Decay Days" (see [settings](#settings)), don't have a redirect and haven't been processed.
It will then decrement the popularity of the term.
If the term is at popularity 0, it will be removed from the table.


## Settings
|Name|Location|Default Value|Description|
|---|---|---|---|
|Decay Days|Stores => Configuration => Catalog | Catalog => Catalog Search|30|The number of days a record must go without use before it's popularity is decremented|


## Commands
### Decay
The decay function can be ran manually
```bash
php bin/magento zero1:search-query-optimization:decay
```
