# PostgreSQL table blameable behavior

[![Build Status](https://travis-ci.org/agilov/pg-blameable.svg)](https://travis-ci.org/agilov/pg-blameable)

PG function that provide blameable behavior on table (setting current db connection username to specified field on UPDATE or INSERT).

Source SQL code in file ./blameable_behavior.sql

## Usage examples

```sql
-- Create trigger that set database connection session username to created_by field
select attach_blameable_behavior('test_table', 'created_by', 'INSERT');

-- Create trigger that set database connection session username to updated_by field
select attach_blameable_behavior('test_table', 'updated_by', 'INSERT OR UPDATE');


-- Deleting blameable behavior trigger for test_table and created_by
select detach_blameable_behavior('test_table', 'created_by');
```
