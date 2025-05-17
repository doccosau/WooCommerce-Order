import { createClient } from "@/lib/supabase/server"
import { redirect } from "next/navigation"

export async function getSession() {
  const supabase = createClient()

  try {
    const { data, error } = await supabase.auth.getSession()
    if (error) throw error
    return data.session
  } catch (error) {
    console.error("Error getting session:", error)
    return null
  }
}

export async function getUserProfile() {
  const supabase = createClient()
  const session = await getSession()

  if (!session) return null

  try {
    const { data, error } = await supabase
      .from("user_profiles")
      .select("*, roles:role_id(name, description)")
      .eq("id", session.user.id)
      .single()

    if (error) throw error
    return data
  } catch (error) {
    console.error("Error getting user profile:", error)
    return null
  }
}

export async function getUserPermissions() {
  const supabase = createClient()
  const session = await getSession()

  if (!session) return []

  try {
    const { data: userProfile } = await supabase
      .from("user_profiles")
      .select("role_id")
      .eq("id", session.user.id)
      .single()

    if (!userProfile?.role_id) return []

    const { data: rolePermissions } = await supabase
      .from("role_permissions")
      .select(`
        permissions:permission_id (
          name
        )
      `)
      .eq("role_id", userProfile.role_id)

    if (!rolePermissions) return []

    return rolePermissions.map((item) => item.permissions.name)
  } catch (error) {
    console.error("Error getting user permissions:", error)
    return []
  }
}

export async function requireAuth() {
  const session = await getSession()

  if (!session) {
    redirect("/auth/login")
  }

  return session
}

export async function requirePermission(permission: string) {
  const session = await requireAuth()
  const permissions = await getUserPermissions()

  if (!permissions.includes(permission)) {
    redirect("/")
  }

  return session
}
