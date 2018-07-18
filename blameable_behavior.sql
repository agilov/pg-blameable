-- Actual function that performs blameable behavior
CREATE OR REPLACE FUNCTION blameable_behavior_func()
  RETURNS TRIGGER AS $$
DECLARE
  _column TEXT := TG_ARGV [0];
  _json   TEXT;
BEGIN

  _json = '{"' || _column || '":"' || (session_user::TEXT) || '"}';
  NEW := json_populate_record(NEW, _json :: JSON);

  RETURN NEW;
END
$$ LANGUAGE plpgsql;

-- Creates trigger to fill specific columns of table on INSERT or UPDATE events
CREATE OR REPLACE FUNCTION attach_blameable_behavior(_table  REGCLASS,_column TEXT,_event  TEXT DEFAULT 'INSERT')
  RETURNS VOID AS $$
BEGIN
  -- Drop existing triggers if they exist.
  EXECUTE detach_blameable_behavior(_table, _column);

  EXECUTE 'CREATE TRIGGER blameable_behavior_trigger_' || _column || ' BEFORE ' || _event || ' ON ' || _table || ' ' ||
          'FOR EACH ROW ' ||
          'EXECUTE PROCEDURE blameable_behavior_func(' || quote_literal(_column) || ');';

END;
$$ LANGUAGE plpgsql;

-- Detach behavior
CREATE OR REPLACE FUNCTION detach_blameable_behavior(_table REGCLASS, _column TEXT)
  RETURNS VOID AS $$
BEGIN
  EXECUTE 'DROP TRIGGER IF EXISTS blameable_behavior_trigger_' || _column || ' ON ' || _table;
END;
$$ LANGUAGE plpgsql;
