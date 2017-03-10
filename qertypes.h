typedef struct brush_s
{
	struct brush_s    *prev, *next; // links in active/selected
	struct brush_s    *oprev, *onext; // links in entity
	struct entity_s   *owner;
	vec3_t mins, maxs;
	face_t                *brush_faces;

	qboolean bModelFailed;
	//
	// curve brush extensions
	// all are derived from brush_faces
	qboolean patchBrush;
	qboolean hiddenBrush;

	//int nPatchID;

	patchMesh_t *pPatch;
	struct entity_s *pUndoOwner;

	int undoId;                     //undo ID
	int redoId;                     //redo ID
	int ownerId;                //entityId of the owner entity for undo

	// TTimo: this is not legal, we are not supposed to put UI toolkit dependant stuff in the interfaces
	// NOTE: the grouping stuff never worked, there is embryonary code everywhere though
	int numberId;
	void* itemOwner; // GtkCTreeNode* ?

	// brush primitive only
	epair_t *epairs;

	// brush filtered toggle
	bool bFiltered;
	bool bCamCulled;
	bool bBrushDef;
} brush_t;


// NOTE: don't trust this definition!
// you should read float points[..][5]
// see NewWinding definition
// WARNING: don't touch anything to this struct unless you looked into winding.cpp and WINDING_SIZE(pt)
//#define MAX_POINTS_ON_WINDING 64

typedef struct winding_s
{
	int numpoints;
	int maxpoints;
	float points[8][5];             // variable sized
} winding_t;

typedef struct plane_s
{
	vec3_t normal;
	double dist;
	int type;
} plane_t;

// pShader is a shortcut to the shader
// it's only up-to-date after a Brush_Build call
// to initialize the pShader, use QERApp_Shader_ForName(texdef.name)
typedef struct face_s
{
	struct face_s           *next;
	struct face_s           *prev;
	struct face_s           *original;      //used for vertex movement
	vec3_t planepts[3];
	texdef_t texdef;
	plane_t plane;

	// Nurail: Face Undo
	int undoId;
	int redoId;

	winding_t               *face_winding;

	vec3_t d_color;
	vec_t d_shade;
	// calls through here have indirections (pure virtual)
	// it would be good if the rendering loop would avoid scanning there (for the GL binding number for example)
	IShader                 *pShader;
	//++timo FIXME: remove!
	qtexture_t              *d_texture;

	// Timo new brush primit texdef
	brushprimit_texdef_t brushprimit_texdef;

	// cast this one to an IPluginTexdef if you are using it
	// NOTE: casting can be done with a GETPLUGINTEXDEF defined in isurfaceplugin.h
	// TODO: if the __ISURFACEPLUGIN_H_ header is used, use a union { void *pData; IPluginTexdef *pPluginTexdef } kind of thing ?
	void                    *pData;
} face_t;


