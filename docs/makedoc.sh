# Make markdown documentation from "numerista-flavour" PHPDoc.
#
# This shellscript has been designed and tested on the MacOS flavour 
# of sed: It may perform different on other sed versions but should 
# be globally compatible.
#
# Numerista Flavour PHPDoc: 
# All phpdoc blocks must start with /** and end with */
# Blocks may contain * on each line of the doc block. 
#
# Overall, the blocks must follow the structure of:
# - Class and Traits (traits are considered a class); defined with
#   a @class or @trait tag followed by the name. The description must
#    follow below the tag.
# - Optionally a @prototype section containing how the class 
#   is instanciated that goes into the description section.
#  - Class properties section: @properties tag with no arguments.
#  - Methods -  @method tag followed by the function & arguments.
#    The method description must follow below the tag.
#  - Method return values: @return followed by the return value type
#    The description of the return values must be below the tag.
#
# Example: 
#
# /**
# * @class foobar
# * @prototype $object = new foobar($bar=0)
# * This class foos your bars.
# * @properties
# * @property $public $foo = null; Stores $foo for use within all the class methods.
# */
#
# /**
# * @method foo($bar=0)
# * Sets $bar to the expcected value.
# * @param integer $bar Value to set for $bar. Default 0.
# * @return bool
# * True on success, false on failure. 
# */


FILENAME=$1;

# Extract the phpdoc blocks
sed -n -e '/\/\*\*/,/\*\// p' $FILENAME | 
# Remove spaces before the * of the comment blocks that are left beind
sed 's/^ *//g' | 
# and remove the space after the * part of the comment 
sed 's/^\* //g' |
# Remove the top of the doc block (/**)
sed 's/^\/\*\*$//g' |
# And the bottom part (*/)
sed 's/\*\/$//g' | 
# Replace @method something by ### so the method name is encapsulated
#sed 's/^@method \(.*\)/## ``\1``/g' |
sed 's:^@method \([a-zA-Z0-9_-]*\)\(.*\):\n---\n## \1()\n@prototype \1\2:g' |
sed 's:^@staticmethod \([a-zA-Z0-9_-]*\)\(.*\):\n---\n## Static \1()\n@prototype \1\2:g' |
# Start working on the parameter block: Add the totle
sed 's/@parameters/\n### Parameters\n/g' | 
# Add a new line after the end of the block and close the code block.
sed 's/\(@prototype.*\))/\1 \n)\n```\n/g' | 
# Remove spaces between commas
sed '/@prototype.*/s/, /,/g'| 
#Split the arguments into newlines
sed '/@prototype.*/s/,/,\n\t/g' | 
# Add a newline after the first bracket after the function
sed '/^@prototype/s/(/&\n\t/g' | 
# Add a line between the Prototype section and the code prototype and add it as a code block.
sed 's/@prototype/\n### Description\n```\n/g' |
# Set all code variables to monotype:
#sed 's:\$\([^ ]*\):\``$\1``:g'
sed '/^@param/s/\$\([^ ]*\)/\``$\1``\n/g' |
# Now convert @param to an unsigned list
sed 's/^@param/-/g' |
# Lets do the class stuff now
sed 's/@class/# Class/g' |
# And traits
sed 's/@trait/# Trait/g' |
# And the properties section
sed 's/@properties/\n## Properties/g' |
# And rename the property
sed 's/^@property \(.*\)/- ``\1``/g' |
# Quote the return value if present
sed 's/@return \(.*\)/\n### Return ``\1``\n/g'
# sed 's/^@return/### Return/g';